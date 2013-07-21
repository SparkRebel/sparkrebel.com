<?php

namespace PW\UserBundle;

final class Events
{
    const onNewUser    = 'user.create';
    const onUpdateUser = 'user.update';
    const onDeleteUser = 'user.delete';

    const onFollowUser   = 'user.follow';
    const onUnfollowUser = 'user.unfollow';

    private function __construct() {}
}