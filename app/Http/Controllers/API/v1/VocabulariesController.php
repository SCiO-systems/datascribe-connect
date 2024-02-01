<?php

namespace App\Http\Controllers\API\v1;

use DB;
use Exception;
use App\Models\Vocabulary;
use App\Http\Controllers\Controller;
use App\Http\Resources\v1\VocabularyResource;
use App\Http\Services\SCIO\VocabulariesService;
use App\Http\Requests\Vocabularies\ShowVocabularyRequest;
use App\Http\Requests\Vocabularies\CreateVocabularyRequest;
use App\Http\Requests\Vocabularies\DeleteVocabularyRequest;
use App\Http\Requests\Vocabularies\ImportVocabularyRequest;
use App\Http\Requests\Vocabularies\ListVocabulariesRequest;
use App\Http\Requests\Vocabularies\UpdateVocabularyRequest;
use App\Http\Requests\Vocabularies\PublishVocabularyRequest;
use App\Http\Requests\Vocabularies\SearchVocabulariesRequest;

class VocabulariesController extends Controller
{
    protected $vocService;

    public function __construct()
    {
        $this->vocService = new VocabulariesService();
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(ListVocabulariesRequest $request)
    {
        $vocs = $request->user()->vocabularies()->get();

        return VocabularyResource::collection($vocs);
    }

    /**
     * Get multiple vocabularies based on their IDs.
     *
     * @return \Illuminate\Http\Response
     */
    public function getMultiple(ListVocabulariesRequest $request)
    {
        $ids = explode(',', $request->ids);

        $vocs = $request->user()
            ->vocabularies()
            ->whereIn('vocabularies.id', $ids)
            ->pluck('external_id')
            ->toArray();

        return $this->vocService->getVocabularies($vocs);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CreateVocabularyRequest $request)
    {
        DB::beginTransaction();

        try {
            $v = $this->vocService->createVocabulary($request->all());

            $vocabulary = Vocabulary::create([
                'listname' => $request->listname,
                'description' => $request->description,
                'external_id' => data_get($v, 'uuid'),
                'isGlobal' => $request->isGlobal,
                'created_by_id' => $request->user()->id,
            ]);

            $vocabulary->setOwner($request->user());
            $vocabulary->body = $v;
        } catch (Exception $ex) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to create a choice list.',
                'error' => $ex->getMessage(),
            ], 500);
        }

        DB::commit();

        return new VocabularyResource($vocabulary);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(ShowVocabularyRequest $request, Vocabulary $vocabulary)
    {
        $v = $this->vocService->getVocabulary($vocabulary->external_id);

        $vocabulary->body = $v;

        return new VocabularyResource($vocabulary);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateVocabularyRequest $request, Vocabulary $vocabulary)
    {
        DB::beginTransaction();

        try {
            $this->vocService->updateVocabulary(
                $vocabulary->external_id,
                $request->all(),
            );

            $vocabulary->listname = $request->listname;
            $vocabulary->description = $request->description;
            $vocabulary->isGlobal = $request->isGlobal;

            $vocabulary->save();
        } catch (Exception $ex) {
            DB::rollBack();

            return response()->json([
                'message' => 'Failed to update an existing choice list.',
                'error' => $ex->getMessage(),
            ], 500);
        }

        DB::commit();

        return new VocabularyResource($vocabulary);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(DeleteVocabularyRequest $request, Vocabulary $vocabulary)
    {
        DB::beginTransaction();

        try {
            $this->vocService->deleteVocabulary($vocabulary->external_id);
            $vocabulary->delete();
        } catch (Exception $ex) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to delete choice list.',
                'error' => $ex->getMessage(),
            ], 500);
        }

        DB::commit();

        return response()->json([], 204);
    }

    /**
     * Make the vocabulary global.
     *
     */
    public function publish(PublishVocabularyRequest $request, Vocabulary $vocabulary)
    {
        DB::beginTransaction();

        try {
            $v = $this->vocService->getVocabulary($vocabulary->external_id);
            $v['isGlobal'] = true;
            $this->vocService->updateVocabulary($vocabulary->external_id, $v);
            $vocabulary->isGlobal = true;
            $vocabulary->save();
        } catch (Exception $ex) {
            DB::rollBack();
            $vocabulary->isGlobal = false;
            $vocabulary->save();
            return response()->json([
                'message' => 'Failed to publish a choice list.',
                'error' => $ex->getMessage(),
            ], 500);
        }

        DB::commit();
    }

    /**
     * Search for global vocabularies.
     *
     * @param SearchVocabulariesRequest $searchVocabulariesRequest
     * @return void
     */
    public function search(SearchVocabulariesRequest $request)
    {
        return $this->vocService->searchVocabularies($request->term);
    }

    /**
     * Import a vocabulary.
     */
    public function import(ImportVocabularyRequest $request)
    {
        DB::beginTransaction();

        try {
            // Find the first created instance of the global vocabulary.
            $vocabulary = Vocabulary::where('external_id', $request->external_id)
                ->orderBy('created_at', 'asc')
                ->first();

            if (!$vocabulary) {
                throw new Exception('The choice list was not found!', 404);
            }

            if ($vocabulary->users()->where('user_id', $request->user()->id)->exists()) {
                throw new Exception(
                    'A copy of this choice list is already imported for this user.',
                    409
                );
            }

            // Clone the vocabulary and assign the user as the owner.
            $v = $this->vocService->cloneVocabulary($vocabulary->external_id);

            $vocabulary = Vocabulary::create([
                'listname' => data_get($v, 'listname'),
                'description' => data_get($v, 'description'),
                'external_id' => data_get($v, 'uuid'),
                'isGlobal' => data_get($v, 'isGlobal'),
                'created_by_id' => $request->user()->id,
            ]);

            $vocabulary->setOwner($request->user());
            $vocabulary->body = $v;
        } catch (Exception $ex) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to import a choice list.',
                'error' => $ex->getMessage(),
            ], $ex->getCode() > 0 ? $ex->getCode() : 500);
        }

        DB::commit();
    }
}
