<?php

namespace PW\PostBundle;

final class Events
{
    // Post
    const onNewPost    = 'post.create';
    const onUpdatePost = 'post.update';
    const onDeletePost = 'post.delete';

    // Repost
    const onNewRepost  = 'post.repost';

    // Comment
    const onNewComment         = 'comment.create';
    const onUpdateComment      = 'comment.update';
    const onDeleteComment      = 'comment.delete';
    const onNewCommentReply    = 'comment.reply.create';
    const onUpdateCommentReply = 'comment.reply.update';
    const onDeleteCommentReply = 'comment.reply.delete';

    private function __construct() {}
}