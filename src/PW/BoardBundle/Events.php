<?php

namespace PW\BoardBundle;

final class Events
{
    const onNewBoard    = 'board.create';
    const onUpdateBoard = 'board.update';
    const onDeleteBoard = 'board.delete';

    private function __construct() {}
}