<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace koperdog\yii2nsblog\interfaces;

/**
 *
 * @author Koperdog <koperdog.dev@gmail.com>
 */
interface BlogHelper {
    public static function get(int $id): ?array;
}
