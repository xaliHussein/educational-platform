<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Traits\Filter;
use App\Traits\Search;
use App\Traits\OrderBy;
use App\Traits\Pagination;
use App\Traits\SendResponse;
use Illuminate\Http\Request;
use App\Events\CommentLessons;
use App\Models\LessonsComments;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class LessonsCommentsController extends Controller
{
    use SendResponse;
    use Pagination;
    use Filter;
    use OrderBy;
    use Search;

    public function getUserLessonsComments()
    {
        $isProfessor = auth()->user()->user_type == 0 || auth()->user()->user_type == 1 ? true : false;
        if ($isProfessor) {
            $lessons_comments = LessonsComments::where("lessons_id", $_GET["lessons_id"])
                ->whereNull('parent_comment_id') // Only top-level lessons_comments
                ->orderBy('created_at', 'ASC') // Ensure top-level lessons_comments are ordered
                ->with(['children' => function ($query) {
                    $query->orderBy('created_at', 'ASC'); // Order child lessons_comments by creation date
                }]);
        } else {
            $lessons_comments = LessonsComments::where('lessons_id', $_GET['lessons_id'])
            ->whereNull('parent_comment_id') // Only top-level lessons_comments
            ->where(function ($query) {
                $query->where('user_id', auth()->id()) // Student's own top-level lessons_comments
                    ->orWhereHas('children', function ($subQuery) {
                        // Only show professor's replies to the student's lessons_comments
                        $subQuery->where('is_professor', true)
                            ->whereIn('parent_comment_id', function ($q) {
                                $q->select('id')
                                    ->from('lessons_comments')
                                    ->where('user_id', auth()->id()); // The student's lessons_comments
                            });
                    });
            })
            ->orderBy('created_at', 'ASC')
            ->with(['children' => function ($query) {
                $query->orderBy('created_at', 'ASC'); // Order child lessons_comments by creation date
            }]);
        }

        if (!isset($_GET['skip'])) {
            $_GET['skip'] = 0;
        }
        if (!isset($_GET['limit'])) {
            $_GET['limit'] = 10;
        }

        $res = $this->paging($lessons_comments->orderBy("created_at", "ASC"), $_GET['skip'], $_GET['limit']);
        return $this->send_response(200, 'تم احضار جميع التعاليق بنجاح', [], $res["model"], null, $res["count"]);
    }

    public function addLessonsComment(Request $request)
    {
        $request = $request->json()->all();
        $validator = Validator::make($request, [
           'content' => 'required|string|max:500',
           'lessons_id' => 'required|exists:lessons,id',
        ], [
            'content.required' => 'يرجى ادخال  تعليق',
            'content.max' => 'الحد الاقصى لعدد الاحرف هوه 500 حرف',
            'lessons_id.required' => 'لم يتم اضافة معرف',
            'lessons_id.exists' => 'هذا الدرس غير موجود',
        ]);

        if ($validator->fails()) {
            return $this->send_response(400, "حصل خطأ في المدخلات", $validator->errors(), []);
        }
        $data = [];
        $data['content'] = $request['content'];
        $data['lessons_id'] = $request['lessons_id'];
        $data['user_id'] = auth()->user()->id;
        $data['is_professor'] = auth()->user()->user_type == 0 || auth()->user()->user_type == 1 ? true : false;

        $lessons_comment = LessonsComments::create($data);
        broadcast(new CommentLessons($lessons_comment, $lessons_comment->lessons_id));
        return $this->send_response(200, 'تم عملية اضافة تعليق بنجاح', [], LessonsComments::find($lessons_comment->id));
    }

    public function replyLessonsComment(Request $request)
    {
        $request = $request->json()->all();
        $validator = Validator::make($request, [
           'content' => 'required|string|max:500',
           'lessons_id' => 'required|exists:lessons,id',
           'parent_comment_id' => 'required|exists:lessons_comments,id',
        ], [
            'content.required' => 'يرجى ادخال  تعليق',
            'content.max' => 'الحد الاقصى لعدد الاحرف هوه 500 حرف',
            'lessons_id.required' => 'لم يتم اضافة معرف',
            'lessons_id.exists' => 'هذا الدرس غير موجود',
            'parent_comment_id.required' => 'لم يتم اضافة معرف التعليق الاصلي',
            'parent_comment_id.exists' => 'التعليق الاصلي غير موجود',
        ]);


        $data = [];
        $data['content'] = $request['content'];
        $data['lessons_id'] = $request['lessons_id'];
        $data['parent_comment_id'] = $request['parent_comment_id'];
        $data['user_id'] = auth()->user()->id;
        $data['is_professor'] = auth()->user()->user_type == 0 || auth()->user()->user_type == 1 ? true : false;

        $lessons_comment = LessonsComments::create($data);
        broadcast(new CommentLessons($lessons_comment, $lessons_comment->lessons_id));
        return $this->send_response(200, 'تم عملية اضافة رد تعليق بنجاح', [], LessonsComments::find($lessons_comment->id));
    }

    public function deleteLessonsComment(Request $request)
    {
        $request = $request->json()->all();
        $validator = Validator::make($request, [
           'comment_id' => 'required|exists:lessons_comments,id',
        ], [
            'comment_id.required' => 'يرجى ادخال  تعليق',
            'comment_id.exists' =>  'التعليق غير موجود',
        ]);

        $lessons_comment = LessonsComments::find($request['comment_id']);
        $lessons_comment->children()->delete();
        $lessons_comment->delete();

        return $this->send_response(200, 'تم عملية حذف التعليق بنجاح', [], []);
    }

    public function deleteReplyLessonsComment(Request $request)
    {
        $request = $request->json()->all();
        $validator = Validator::make($request, [
           'comment_id' => 'required|exists:lessons_comments,id',
        ], [
            'comment_id.required' => 'يرجى ادخال  تعليق',
            'comment_id.exists' =>  'التعليق غير موجود',
        ]);

        $lessons_comment = LessonsComments::find($request['comment_id']);
        $lessons_comment->delete();
        return $this->send_response(200, 'تم عملية حذف التعليق بنجاح', [], []);
    }

    public function editLessonsComment(Request $request)
    {
        $request = $request->json()->all();
        $validator = Validator::make($request, [
           'content' => 'required|string|max:500',
           'id' => 'required|exists:lessons_comments,id',
        ], [
            'content.required' => 'يرجى ادخال  تعليق',
            'content.max' => 'الحد الاقصى لعدد الاحرف هوه 500 حرف',
            'id.required' => 'لم يتم اضافة معرف التعليق',
            'id.exists' => 'هذا التعليق غير موجود',
        ]);

        if ($validator->fails()) {
            return $this->send_response(400, "حصل خطأ في المدخلات", $validator->errors(), []);
        }
        $lessons_comment = LessonsComments::find($request['id']);
        $data = [];
        $data['content'] = $request['content'];
        $lessons_comment->update($data);
        broadcast(new CommentLessons($lessons_comment, $lessons_comment->lessons_id));

        return $this->send_response(200, 'تم عملية تعديل التعليق بنجاح', [], LessonsComments::find($lessons_comment->id));
    }
}
