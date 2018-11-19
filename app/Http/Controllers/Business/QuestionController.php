<?php

namespace App\Http\Controllers\Business;

use App\Business;
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
     * @param \App\Business $business
     * @return \Illuminate\Http\Response
     */
    public function index(Business $business)
    {
        $this->authorize('update', $business);
        return response()->json(activeBusiness()->questions);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param CreateQuestionRequest $request
     * @return \Illuminate\Http\Response
     */
    public function store(CreateQuestionRequest $request)
    {
        $data = $request->filtered();
        $this->authorize('update', Business::findOrFail($data['business_id']));

        if ($question = Question::create($data)) {
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
