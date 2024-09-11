<?php

namespace App\Http\Controllers;

use App\Traits\Filter;
use App\Traits\OrderBy;
use App\Traits\Pagination;
use App\Models\Enrollments;
use App\Traits\UploadImage;
use App\Traits\SendResponse;
use Illuminate\Http\Request;
use App\Models\Choice;
use App\Models\Question;
use App\Models\CategoriesQuestion;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class QuestionController extends Controller
{
    use SendResponse;
    use Pagination;
    use Filter;
    use OrderBy;
    use UploadImage;

    public function getCategoriesQuestion()
    {
        $categories_question = CategoriesQuestion::select("*");

        if (!isset($_GET['skip'])) {
            $_GET['skip'] = 0;
        }
        if (!isset($_GET['limit'])) {
            $_GET['limit'] = 10;
        }
        $res = $this->paging($categories_question->orderBy("created_at", "DESC"), $_GET['skip'], $_GET['limit']);
        return $this->send_response(200, 'تم احضار جميع فصول الاسئله', [], $res["model"], null, $res["count"]);
    }

    public function getQuestion()
    {

        $questions = Question::where("category_id", $_GET["category_id"]);

        if (!isset($_GET['skip'])) {
            $_GET['skip'] = 0;
        }
        if (!isset($_GET['limit'])) {
            $_GET['limit'] = 10;
        }

        $res = $this->paging($questions->orderBy("created_at", "DESC"), $_GET['skip'], $_GET['limit']);
        return $this->send_response(200, 'تم احضار جميع الاسئله', [], $res["model"], null, $res["count"]);
    }

    public function addCategoriesQuestion(Request $request)
    {

        $request = $request->json()->all();
        $validator = Validator::make($request, [
            'title' => 'required|string|max:255',
        ], [
            'title.required' => 'اسم القسم مطلوب',
            'title.max' => 'الحد الاقصى لعدد الاحرف هوه 255 حرف',
        ]);
        if ($validator->fails()) {
            return $this->send_response(400, "حصل خطأ في المدخلات", $validator->errors(), []);
        }

        $data['title'] = $request['title'];
        $categories_question = CategoriesQuestion::create($data);

        return $this->send_response(200, 'تم اضافة فصل اسئله بنجاح', [], CategoriesQuestion::find($categories_question->id));
    }
    public function editCategoriesQuestion(Request $request)
    {

        $request = $request->json()->all();
        $validator = Validator::make($request, [
            'id' => 'required|exists:categories_questions,id',
            'title' => 'required|string|max:255',
        ], [
            'id.required' => 'معرف رقم القسم مطلوب',
            'id.exists' => 'معرف رقم القسم غير موجود',
            'title.required' => 'اسم القسم مطلوب',
            'title.max' => 'الحد الاقصى لعدد الاحرف هوه 255 حرف',
        ]);
        if ($validator->fails()) {
            return $this->send_response(400, "حصل خطأ في المدخلات", $validator->errors(), []);
        }

        $categories_question = CategoriesQuestion::find($request['id']);
        $data['title'] = $request['title'];
        $categories_question->update($data);

        return $this->send_response(200, 'تم تعديل فصل اسئله بنجاح', [], CategoriesQuestion::find($categories_question->id));
    }

    public function addQuestion(Request $request)
    {

        $request = $request->json()->all();
        $validator = Validator::make($request, [
            'question_text' => 'required|string|max:1250',
            'category_id' => 'required|exists:categories_questions,id',
            'multi_choices.*.choice_text' => 'required|string|max:1250',
            'multi_choices.*.is_correct' => 'boolean',
            'multi_choices.*.is_correct_text' => 'nullable|string|max:1250|sometimes',
        ], [
            'question_text.required' => 'يرجى ادخال السوال',
            'category_id.required' => 'معرف رقم القسم مطلوب',
            'category_id.exists' => 'معرف رقم القسم غير موجود',

            'question_text.max' => 'الحد الاقصى لعدد الاحرف هوه 1250 حرف',
            'multi_choices.*.is_correct_text.max' => 'الحد الاقصى لعدد الاحرف الاجابه الصحيحه هوه 1250 حرف',
            'multi_choices.*.choice_text.required' => 'يجب ادخال نص الاختيار',
            'multi_choices.*.choice_text.max' => 'الحد الاقصى لعدد الاحرف نص الاختيار  هوه 1250 حرف',
        ]);
        if ($validator->fails()) {
            return $this->send_response(400, "حصل خطأ في المدخلات", $validator->errors(), []);
        }

        $data['category_id'] = $request['category_id'];
        $data['question_text'] = $request['question_text'];
        $question = Question::create($data);

        foreach ($request['multi_choices'] as $choice) {
            $question->choices()->create([
                'choice_text' => $choice['choice_text'],
                'is_correct' => $choice['is_correct'],
                'is_correct_text' => $choice['is_correct_text'],
            ]);
        }

        return $this->send_response(200, 'تم انشاء سوال  MCQ بنجاح', [], Question::find($question->id));
    }
    public function editQuestion(Request $request)
    {

        $request = $request->json()->all();
        $validator = Validator::make($request, [
            'id' => 'required|exists:questions,id',
            'question_text' => 'required|string|max:1250',
            'category_id' => 'required|exists:categories_questions,id',
            'multi_choices.*.id' => 'exists:choices,id',
            'multi_choices.*.choice_text' => 'required|string|max:1250',
            'multi_choices.*.is_correct' => 'boolean',
            'multi_choices.*.is_correct_text' => 'nullable|string|max:1250|sometimes',
        ], [
            'id.required' => 'معرف السوال مطلوب',
            'id.exists' => 'معرف السوال غير موجود',

            'question_text.required' => 'يرجى ادخال السوال',
            'category_id.required' => 'معرف القسم مطلوب',
            'category_id.exists' => 'معرف القسم غير موجود',

            'question_text.max' => 'الحد الاقصى لعدد الاحرف هوه 1250 حرف',
            'multi_choices.*.id.required' => 'معرف الاختيار مطلوب',
            'multi_choices.*.id.exists' => 'معرف الاختيار غير موجود',

            'multi_choices.*.is_correct_text.max' => 'الحد الاقصى لعدد الاحرف الاجابه الصحيحه هوه 1250 حرف',
            'multi_choices.*.choice_text.required' => 'يجب ادخال نص الاختيار',
            'multi_choices.*.choice_text.max' => 'الحد الاقصى لعدد الاحرف نص الاختيار  هوه 1250 حرف',
        ]);
        if ($validator->fails()) {
            return $this->send_response(400, "حصل خطأ في المدخلات", $validator->errors(), []);
        }

        $question = Question::find($request['id']);

        $data['category_id'] = $request['category_id'];
        $data['question_text'] = $request['question_text'];
        $question->update($data);

        foreach ($request['multi_choices'] as $choiceData) {
            if (isset($choiceData['id'])) {
                // Update existing choice
                $choice = Choice::find($choiceData['id']);
                $choice->update([
                    'choice_text' => $choiceData['choice_text'],
                    'is_correct' => $choiceData['is_correct'],
                    'is_correct_text' => $choiceData['is_correct_text'],
                ]);
                $existingChoiceIds[] = $choiceData['id'];
            } else {
                // Create new choice
                $question->choices()->create([
                    'choice_text' => $choiceData['choice_text'],
                    'is_correct' => $choiceData['is_correct'],
                    'is_correct_text' => $choiceData['is_correct_text'],
                ]);
            }
        }
        // Delete choices that are not in the request anymore
        $question->choices()->whereNotIn('id', $existingChoiceIds)->delete();

        return $this->send_response(200, 'تم تعديل سوال  MCQ بنجاح', [], Question::find($question->id));
    }

    public function showResultQuestion(Request $request)
    {

        $request = $request->json()->all();
        $validator = Validator::make($request, [
            'id' => 'required|exists:questions,id',

        ], [
            'id.required' => 'معرف السوال مطلوب',
            'id.exists' =>  'هذا السوال غير موجود',
        ]);
        if ($validator->fails()) {
            return $this->send_response(400, "حصل خطأ في المدخلات", $validator->errors(), []);
        }

        $question = Question::find($request['id']);

        $data['show_result'] = true;
        $question->update($data);

        return $this->send_response(200, 'تم اظهار اجابة السوال بنجاح', [], Question::find($question->id));
    }

    public function deleteQuestion(Request $request)
    {
        $request = $request->json()->all();
        $validator = Validator::make($request, [
            'id' => 'required|exists:questions,id',
        ], [
            'id.required' => 'معرف السوال مطلوب',
            'id.exists' =>  'هذا السوال غير موجود',
        ]);
        if ($validator->fails()) {
            return $this->send_response(400, "حصل خطأ في المدخلات", $validator->errors(), []);
        }

        $question = Question::find($request['id']);
        $question->choices()->delete();
        $question->delete();

        return $this->send_response(200, 'تم حذف سوال MCQ بنجاح', [], []);
    }

    public function deleteCategoriesQuestion(Request $request)
    {
        $request = $request->json()->all();
        $validator = Validator::make($request, [
            'id' => 'required|exists:categories_questions,id',
        ], [
            'id.required' => 'معرف الفصل مطلوب',
            'id.exists' =>  'هذا الفصل غير موجود',
        ]);
        if ($validator->fails()) {
            return $this->send_response(400, "حصل خطأ في المدخلات", $validator->errors(), []);
        }

        $categories_question = CategoriesQuestion::find($request['id']);

        foreach ($categories_question->question as $question) {
            $question->choices()->delete();
            $question->delete();
        }

        $categories_question->delete();


        return $this->send_response(200, 'تم حذف الفصل مع الاسئلة بنجاح', [], []);
    }


}
