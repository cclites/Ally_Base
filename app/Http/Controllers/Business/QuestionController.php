<?php

namespace App\Http\Controllers\Business;

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
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
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
        if ($question = activeBusiness()->questions()->create($request->validated())) {
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
        if ($question->business_id != activeBusiness()->id) {
            return new ErrorResponse(403, 'You do not have access to that question.');
        }

        if ($question->update($request->validated())) {
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
        if ($question->business_id != activeBusiness()->id) {
            return new ErrorResponse(403, 'You do not have access to that question.');
        }

        if ($question->delete()) {
            return new SuccessResponse('The question has been deleted.');
        }

        return new ErrorResponse(500, 'Could not delete the Question.  Please try again.');
    }
}
