<?php

/**
 * Copyright (C) 2017 Bynq.io B.V.
 * All Rights Reserved
 *
 * This file is part of the BynqIO\CalculatieTool.com.
 *
 * Content can not be copied and/or distributed without the express
 * permission of the author.
 *
 * @package  CalculatieTool
 * @author   Yorick de Wid <y.dewid@calculatietool.com>
 */

namespace BynqIO\CalculatieTool\Core\Component;

use BynqIO\CalculatieTool\Core\Contracts\Component;

/**
 * Class DetailComponent.
 */
class DetailComponent extends BaseComponent implements Component
{
    public function render()
    {
        return view("component.{$this->component}");
    }
}
