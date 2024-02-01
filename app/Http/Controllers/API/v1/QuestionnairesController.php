<?php

namespace App\Http\Controllers\API\v1;

use DB;
use Exception;
use App\Models\User;
use App\Models\Questionnaire;
use App\Http\Controllers\Controller;
use App\Http\Resources\v1\QuestionnaireResource;
use App\Http\Services\SCIO\QuestionnaireService;
use App\Http\Services\SCIO\PreviewService;
use App\Http\Requests\Questionnaires\ShowQuestionnaireRequest;
use App\Http\Requests\Questionnaires\CloneQuestionnaireRequest;
use App\Http\Requests\Questionnaires\ListQuestionnairesRequest;
use App\Http\Requests\Questionnaires\CreateQuestionnaireRequest;
use App\Http\Requests\Questionnaires\DeleteQuestionnaireRequest;
use App\Http\Requests\Questionnaires\ImportQuestionnaireRequest;
use App\Http\Requests\Questionnaires\UpdateQuestionnaireRequest;
use App\Http\Requests\Questionnaires\ChangeQuestionnaireOwnerRequest;

class QuestionnairesController extends Controller
{
    protected $questionnaireService;

    public function __construct()
    {
        $this->previewService = new PreviewService();
        $this->questionnaireService = new QuestionnaireService();
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(ListQuestionnairesRequest $request)
    {
        $questionnaires = $request->user()->questionnaires()->get();

        return QuestionnaireResource::collection($questionnaires);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CreateQuestionnaireRequest $request)
    {
        DB::beginTransaction();

        try {
            $q = $this->questionnaireService->createQuestionnaire($request->getContent());

            $questionnaire = Questionnaire::create([
                'title' => data_get($q, 'metadata.title'),
                'external_id' => data_get($q, 'uuid'),
                'language' => data_get($q, 'metadata.language.name'),
                'version' => data_get($q, 'metadata.version'),
                'created_by_id' => $request->user()->id,
            ]);

            $this->previewService->preparePreview($questionnaire);
            $questionnaire->setOwner($request->user());
            $questionnaire->body = $q;
        } catch (Exception $ex) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to create questionnaire.',
                'error' => $ex->getMessage(),
            ], 500);
        }

        DB::commit();

        return new QuestionnaireResource($questionnaire);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(ShowQuestionnaireRequest $request, Questionnaire $questionnaire)
    {
        $q = $this->questionnaireService->getQuestionnaire($questionnaire->external_id);

        $questionnaire->load('users');
        $questionnaire->title = data_get($q, 'metadata.title');
        $questionnaire->body = $q;

        return new QuestionnaireResource($questionnaire);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateQuestionnaireRequest $request, Questionnaire $questionnaire)
    {
        DB::beginTransaction();

        try {
            $this->questionnaireService->updateQuestionnaire(
                $questionnaire->external_id,
                $request->getContent(),
            );
            $this->previewService->preparePreview($questionnaire);
            $questionnaire->title = data_get($request->all(), 'metadata.title');
            $questionnaire->language = data_get($request->all(), 'metadata.language.name');
            $questionnaire->version = data_get($request->all(), 'metadata.version');
            $questionnaire->save();
        } catch (Exception $ex) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to update questionnaire.',
                'error' => $ex->getMessage(),
            ], 500);
        }

        DB::commit();

        return new QuestionnaireResource($questionnaire);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(DeleteQuestionnaireRequest $request, Questionnaire $questionnaire)
    {
        DB::beginTransaction();

        try {
            $this->questionnaireService->deleteQuestionnaire($questionnaire->external_id);
            $questionnaire->users()->detach();
            $questionnaire->delete();
        } catch (Exception $ex) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to delete questionnaire.',
                'error' => $ex->getMessage(),
            ], 500);
        }

        DB::commit();

        return response()->json([], 204);
    }

    /**
     * Change the owner of the questionnaire.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     */
    public function changeOwnership(
        ChangeQuestionnaireOwnerRequest $request,
        Questionnaire $questionnaire
    ) {
        $questionnaire->setOwner(User::find($request->owner_id));
        $questionnaire->setViewer($request->user());

        return response()->json([], 204);
    }

    /**
     * Download a questionnaire.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     */
    public function download(ShowQuestionnaireRequest $request, Questionnaire $questionnaire)
    {
        return $this->questionnaireService->downloadQuestionnaire($questionnaire->external_id);
    }

    /**
     * Clone a questionnaire.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     */
    public function clone(CloneQuestionnaireRequest $request, Questionnaire $questionnaire)
    {
        DB::beginTransaction();

        try {
            $q = $this->questionnaireService->cloneQuestionnaire($questionnaire->external_id);

            $questionnaire = Questionnaire::create([
                'title' => data_get($q, 'metadata.title'),
                'external_id' => data_get($q, 'uuid'),
                'language' => data_get($q, 'metadata.language.name'),
                'version' => data_get($q, 'metadata.version'),
                'created_by_id' => $request->user()->id,
            ]);

            $questionnaire->setOwner($request->user());
            $questionnaire->body = $q;
        } catch (Exception $ex) {
            return response()->json([
                'message' => 'Failed to clone questionnaire.',
                'error' => $ex->getMessage(),
            ], 500);
        }

        DB::commit();

        return new QuestionnaireResource($questionnaire);
    }

    /**
     * Clone a questionnaire.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     */
    public function import(ImportQuestionnaireRequest $request)
    {
        $q = null;
        try {
            $q = $this->questionnaireService->importQuestionnaire(
                $request->file('file')->getContent()
            );
        } catch (Exception $ex) {
            return response()->json([
                'message' => 'Failed to import questionnaire.',
                'error' => $ex->getMessage(),
            ], 500);
        }

        return response()->json($q);
    }

    /**
     * Download carob script for questionnaire.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     */
    public function downloadCarobScript(
        ShowQuestionnaireRequest $request,
        Questionnaire $questionnaire
    ) {
        return $this->questionnaireService->downloadCarobScript($questionnaire->external_id);
    }
}
