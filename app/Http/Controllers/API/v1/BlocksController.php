<?php

namespace App\Http\Controllers;

namespace App\Http\Controllers\API\v1;

use DB;
use Exception;
use App\Models\Block;
use App\Http\Controllers\Controller;
use App\Http\Resources\v1\BlockResource;
use App\Http\Services\SCIO\BlocksService;
use App\Http\Requests\Blocks\PublishBlockRequest;
use App\Http\Requests\Blocks\ViewBlockRequest;
use App\Http\Requests\Blocks\ListBlocksRequest;
use App\Http\Requests\Blocks\CreateBlockRequest;
use App\Http\Requests\Blocks\DeleteBlockRequest;
use App\Http\Requests\Blocks\ImportBlockRequest;
use App\Http\Requests\Blocks\UpdateBlockRequest;
use App\Http\Requests\Blocks\SearchBlocksRequest;

class BlocksController extends Controller
{
    protected $blocksService;

    public function __construct()
    {
        $this->blocksService = new BlocksService();
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(ListBlocksRequest $request)
    {
        $vocs = $request->user()->blocks()->get();

        return BlockResource::collection($vocs);
    }

    /**
     * Get multiple vocabularies based on their IDs.
     *
     * @return \Illuminate\Http\Response
     */
    public function getMultiple(ListBlocksRequest $request)
    {
        $ids = explode(',', $request->ids);

        $blocks = $request->user()
            ->blocks()
            ->whereIn('blocks.id', $ids)
            ->pluck('external_id')
            ->toArray();

        return $this->blocksService->getBlocks($blocks);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CreateBlockRequest $request)
    {
        DB::beginTransaction();

        try {
            $b = $this->blocksService->createBlock($request->all());

            $block = Block::create([
                'name' => $request->metadata['name'],
                'description' => $request->metadata['description'],
                'external_id' => data_get($b, 'uuid'),
                'isGlobal' => false,
                'created_by_id' => $request->user()->id,
            ]);

            $block->setOwner($request->user());
            $block->body = $b;
        } catch (Exception $ex) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to create a block.',
                'error' => $ex->getMessage(),
            ], 500);
        }

        DB::commit();

        return new BlockResource($block);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(ViewBlockRequest $request, Block $block)
    {
        $b = $this->blocksService->getBlock($block->external_id);

        $block->body = $b;

        return new BlockResource($block);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateBlockRequest $request, Block $block)
    {
        DB::beginTransaction();

        try {
            $this->blocksService->updateBlock(
                $block->external_id,
                $request->all(),
            );

            $block->name = $request->metadata['name'];
            $block->description = $request->metadata['description'];
            $block->isGlobal = $request->isGlobal;

            $block->save();
        } catch (Exception $ex) {
            DB::rollBack();

            return response()->json([
                'message' => 'Failed to update an existing block.',
                'error' => $ex->getMessage(),
            ], 500);
        }

        DB::commit();

        return new BlockResource($block);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(DeleteBlockRequest $request, Block $block)
    {
        DB::beginTransaction();

        try {
            $this->blocksService->deleteBlock($block->external_id);
            $block->delete();
        } catch (Exception $ex) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to delete block.',
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
    public function publish(PublishBlockRequest $request, Block $block)
    {
        DB::beginTransaction();

        try {
            $v = $this->blocksService->getBlock($block->external_id);
            $v['isGlobal'] = true;
            $this->blocksService->updateBlock($block->external_id, $v);
            $block->isGlobal = true;
            $block->save();
        } catch (Exception $ex) {
            DB::rollBack();
            $block->isGlobal = false;
            $block->save();
            return response()->json([
                'message' => 'Failed to publish a block.',
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
    public function search(SearchBlocksRequest $request)
    {
        return $this->blocksService->searchBlocks($request->term);
    }

    /**
     * Import a vocabulary.
     */
    public function import(ImportBlockRequest $request)
    {
        DB::beginTransaction();

        try {
            // Find the first created instance of the global vocabulary.
            $block = Block::where('external_id', $request->external_id)
                ->orderBy('created_at', 'asc')
                ->first();

            if (!$block) {
                throw new Exception('The block was not found!', 404);
            }

            if ($block->users()->where('user_id', $request->user()->id)->exists()) {
                throw new Exception(
                    'A copy of this block is already imported for this user.',
                    409
                );
            }

            // Clone the vocabulary and assign the user as the owner.
            $b = $this->blocksService->cloneBlock($block->external_id);

            $block = Block::create([
                'name' => data_get($b, 'metadata.name'),
                'description' => data_get($b, 'metadata.description'),
                'external_id' => data_get($b, 'uuid'),
                'isGlobal' => data_get($b, 'isGlobal'),
                'created_by_id' => $request->user()->id,
            ]);

            $block->setOwner($request->user());
            $block->body = $b;
        } catch (Exception $ex) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to import block.',
                'error' => $ex->getMessage(),
            ], $ex->getCode() > 0 ? $ex->getCode() : 500);
        }

        DB::commit();
    }
}
