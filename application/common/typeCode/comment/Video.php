<?php
/**
 * Created by PhpStorm.
 * User: 刘彪
 * Date: 2019/10/29
 * Time: 16:54
 */
namespace app\common\typeCode\comment;


use app\common\typeCode\impl\CommentImpl;

class Video implements CommentImpl
{
    private $commentType = 1;

    public function getCommentType(): int
    {
        // TODO: Implement getCommentType() method.

        return $this->commentType;
    }
}