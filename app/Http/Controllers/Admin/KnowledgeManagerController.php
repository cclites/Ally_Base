<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Knowledge;
use App\Http\Requests\StoreKnowledgeRequest;
use App\Responses\SuccessResponse;
use App\Http\Requests\UpdateKnowledgeRequest;
use App\Responses\ErrorResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class KnowledgeManagerController extends Controller
{
    /**
     * Get listing of all Knowledge Items.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $knowledge = Knowledge::ordered()->get();

        return view('admin.knowledge-manager.index')->with(compact(['knowledge']));
    }

    /**
     * Show create Knowledge Item form.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $knowledge = null;
        return view('admin.knowledge-manager.edit')->with(compact(['knowledge']));
    }

    /**
     * Insert new Knowledge Item.
     *
     * @param StoreKnowledgeRequest $request
     * @return SuccessResponse|ErrorResponse
     */
    public function store(StoreKnowledgeRequest $request)
    {
        $data = $request->validated();

        $attachments = collect($data['attachments'])->pluck('id')->toArray();

        try {
            DB::beginTransaction();
            $item = Knowledge::create(Arr::except($data, ['attachments', 'assigned_roles']));
            $item->attachments()->sync($attachments);
            $item->syncRoles($data['assigned_roles']);
        } catch (\Exception $ex) {
            DB::rollBack();
            return new ErrorResponse(500, "An unexpected error occurred while creating the Knowledge Item, please try again.");
        }

        DB::commit();

        return new SuccessResponse(
            "\"{$item->title}\" has been published.",
            null,
            route('admin.knowledge.edit', ['knowledge' => $item->id])
        );
    }

    /**
     * Show edit Knowledge Item form.
     *
     * @param Knowledge $knowledge
     * @return \Illuminate\Http\Response
     */
    public function edit(Knowledge $knowledge)
    {
        return view('admin.knowledge-manager.edit')->with(compact(['knowledge']));
    }

    /**
     * Update knowledge item.
     *
     * @param UpdateKnowledgeRequest $request
     * @param Knowledge $knowledge
     * @return ErrorResponse|SuccessResponse
     * @throws \Exception
     */
    public function update(UpdateKnowledgeRequest $request, Knowledge $knowledge)
    {
        $data = $request->validated();

        $attachments = collect($data['attachments'])->pluck('id')->toArray();

        DB::beginTransaction();
        if ($knowledge->update(Arr::except($data, ['attachments', 'assigned_roles']))) {
            $knowledge->attachments()->sync($attachments);
            $knowledge->syncRoles($data['assigned_roles']);

            DB::commit();
            return new SuccessResponse("\"{$knowledge->title}\" has been published.", $knowledge->fresh());
        }

        DB::rollBack();
        return new ErrorResponse(500, 'An unexpected error occurred.');
    }

    /**
     * Delete the Knowledge Item.
     *
     * @param Knowledge $knowledge
     * @return ErrorResponse|SuccessResponse
     * @throws \Exception
     */
    public function destroy(Knowledge $knowledge)
    {
        if ($knowledge->delete()) {
            return new SuccessResponse("\"{$knowledge->title}\" has been deleted.", Knowledge::all());
        }

        return new ErrorResponse(500, 'An unexpected error occurred.');
    }
}
