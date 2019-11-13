<?php

namespace App\Http\Controllers\Business;

use App\Business;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Question;
use App\Responses\SuccessResponse;
use App\Http\Requests\CreateQuestionRequest;
use App\Responses\ErrorResponse;

class QuestionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     * @throws AuthorizationException
     */
    public function index(Request $request)
    {
        $business = Business::findOrFail($request->business);
        $this->authorize('update', $business);

        return response()->json($business->questions);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param CreateQuestionRequest $request
     * @return \Illuminate\Http\Response
     * @throws AuthorizationException
     */
    public function store(CreateQuestionRequest $request)
    {
        $business = $request->getBusiness();
        $this->authorize('update', $business);

        if ($question = $business->questions()->create($request->filtered())) {
            return new SuccessResponse('Question has been created.', $question);
        }

        return new ErrorResponse(500, 'Could not create the Question.  Please try again.');
    }

    /**
     * Update the Question.
     *
     * @param CreateQuestionRequest $request
     * @param Question $question
     * @return \Illuminate\Http\Response
     * @throws AuthorizationException
     */
    public function update(CreateQuestionRequest $request, Question $question)
    {
        $data = $request->filtered();
        $this->authorize('update', $question->business);

        if ($question->update($data)) {
            return new SuccessResponse('Question has been saved.', $question->fresh());
        }

        return new ErrorResponse(500, 'Could not save the Question.  Please try again.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Question  $question
     * @return \Illuminate\Http\Response
     * @throws AuthorizationException
     * @throws \Exception
     */
    public function destroy(Question $question)
    {
        $this->authorize('update', $question->business);

        if ($question->delete()) {
            return new SuccessResponse('The question has been deleted.');
        }

        return new ErrorResponse(500, 'Could not delete the Question.  Please try again.');
    }
}
