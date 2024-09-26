<?php

namespace App\Http\Controllers;

use App\Models\News;
use App\Models\User;
use App\Models\Comments;
use App\Events\CommentNews;
use App\Traits\Filter;
use App\Traits\Search;
use App\Traits\OrderBy;
use App\Traits\Pagination;
use Illuminate\Support\Str;
use App\Traits\SendResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class CommentsController extends Controller
{
    use SendResponse;
    use Pagination;
    use Filter;
    use OrderBy;
    use Search;

    public function getUserComments()
    {
        $isProfessor = auth()->user()->user_type == 0 || auth()->user()->user_type == 1 ? true : false;
        if ($isProfessor) {
            $comments = Comments::where("news_id", $_GET["news_id"])
                ->whereNull('parent_comment_id') // Only top-level comments
                ->orderBy('created_at', 'ASC') // Ensure top-level comments are ordered
                ->with(['children' => function ($query) {
                    $query->orderBy('created_at', 'ASC'); // Order child comments by creation date
                }]);
        } else {
            $comments = Comments::where('news_id', $_GET['news_id'])
            ->whereNull('parent_comment_id') // Only top-level comments
            ->where(function ($query) {
                $query->where('user_id', auth()->id()) // Student's own top-level comments
                    ->orWhereHas('children', function ($subQuery) {
                        // Only show professor's replies to the student's comments
                        $subQuery->where('is_professor', true)
                            ->whereIn('parent_comment_id', function ($q) {
                                $q->select('id')
                                    ->from('comments')
                                    ->where('user_id', auth()->id()); // The student's comments
                            });
                    });
            })
            ->orderBy('created_at', 'ASC')
            ->with(['children' => function ($query) {
                $query->orderBy('created_at', 'ASC'); // Order child comments by creation date
            }]);
        }

        if (!isset($_GET['skip'])) {
            $_GET['skip'] = 0;
        }
        if (!isset($_GET['limit'])) {
            $_GET['limit'] = 10;
        }

        $res = $this->paging($comments->orderBy("created_at", "ASC"), $_GET['skip'], $_GET['limit']);
        return $this->send_response(200, 'تم احضار جميع التعاليق بنجاح', [], $res["model"], null, $res["count"]);
    }

    public function addComment(Request $request)
    {
        $request = $request->json()->all();
        $validator = Validator::make($request, [
           'content' => 'required|string|max:500',
           'news_id' => 'required|exists:news,id',
        ], [
            'content.required' => 'يرجى ادخال  تعليق',
            'content.max' => 'الحد الاقصى لعدد الاحرف هوه 500 حرف',
            'news_id.required' => 'لم يتم اضافة معرف',
            'news_id.exists' => 'هذا الخبر غير موجود',
        ]);

        if ($validator->fails()) {
            return $this->send_response(400, "حصل خطأ في المدخلات", $validator->errors(), []);
        }
        $data = [];
        $data['content'] = $request['content'];
        $data['news_id'] = $request['news_id'];
        $data['user_id'] = auth()->user()->id;
        $data['is_professor'] = auth()->user()->user_type == 0 || auth()->user()->user_type == 1 ? true : false;

        $comment = Comments::create($data);

        broadcast(new CommentNews($comment,$comment->news_id,auth()->user()->id));

        return $this->send_response(200, 'تم عملية اضافة تعليق بنجاح', [], Comments::find($comment->id));
    }

    public function replyComment(Request $request)
    {
        $request = $request->json()->all();
        $validator = Validator::make($request, [
           'content' => 'required|string|max:500',
           'news_id' => 'required|exists:news,id',
           'parent_comment_id' => 'required|exists:comments,id',
        ], [
            'content.required' => 'يرجى ادخال  تعليق',
            'content.max' => 'الحد الاقصى لعدد الاحرف هوه 500 حرف',
            'news_id.required' => 'لم يتم اضافة معرف',
            'news_id.exists' => 'هذا الخبر غير موجود',
            'parent_comment_id.required' => 'لم يتم اضافة معرف التعليق الاصلي',
            'parent_comment_id.exists' => 'التعليق الاصلي غير موجود',
        ]);


        $data = [];
        $data['content'] = $request['content'];
        $data['news_id'] = $request['news_id'];
        $data['parent_comment_id'] = $request['parent_comment_id'];
        $data['user_id'] = auth()->user()->id;
        $data['is_professor'] = auth()->user()->user_type == 0 || auth()->user()->user_type == 1 ? true : false;

        $comment = Comments::create($data);
        broadcast(new CommentNews($comment,$comment->news_id,auth()->user()->id));
        return $this->send_response(200, 'تم عملية اضافة رد تعليق بنجاح', [], Comments::find($comment->id));
    }

    public function deleteComment(Request $request)
    {
        $request = $request->json()->all();
        $validator = Validator::make($request, [
           'comment_id' => 'required|exists:comments,id',
        ], [
            'comment_id.required' => 'يرجى ادخال  تعليق',
            'comment_id.exists' =>  'التعليق غير موجود',
        ]);

        $comment = Comments::find($request['comment_id']);
        $comment->children()->delete();
        $comment->delete();

        return $this->send_response(200, 'تم عملية حذف التعليق بنجاح', [], []);
    }

    public function deleteReplyComment(Request $request)
    {
        $request = $request->json()->all();
        $validator = Validator::make($request, [
           'comment_id' => 'required|exists:comments,id',
        ], [
            'comment_id.required' => 'يرجى ادخال  تعليق',
            'comment_id.exists' =>  'التعليق غير موجود',
        ]);

        $comment = Comments::find($request['comment_id']);
        $comment->delete();
        return $this->send_response(200, 'تم عملية حذف التعليق بنجاح', [], []);
    }

    public function editComment(Request $request)
    {
        $request = $request->json()->all();
        $validator = Validator::make($request, [
           'content' => 'required|string|max:500',
           'id' => 'required|exists:comments,id',
        ], [
            'content.required' => 'يرجى ادخال  تعليق',
            'content.max' => 'الحد الاقصى لعدد الاحرف هوه 500 حرف',
            'id.required' => 'لم يتم اضافة معرف التعليق',
            'id.exists' => 'هذا التعليق غير موجود',
        ]);

        if ($validator->fails()) {
            return $this->send_response(400, "حصل خطأ في المدخلات", $validator->errors(), []);
        }
        $comment = Comments::find($request['id']);
        $data = [];
        $data['content'] = $request['content'];
        $comment->update($data);

        broadcast(new CommentNews($comment,$comment->news_id));

        return $this->send_response(200, 'تم عملية تعديل التعليق بنجاح', [], Comments::find($comment->id));
    }

}
