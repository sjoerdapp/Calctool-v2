<?php

/**
 * Copyright (C) 2017 Bynq.io B.V.
 * All Rights Reserved
 *
 * This file is part of the Dynq project.
 *
 * Content can not be copied and/or distributed without the express
 * permission of the author.
 *
 * @package  Dynq
 * @author   Yorick de Wid <y.dewid@calculatietool.com>
 */

namespace BynqIO\Dynq\ProjectManager\Component;

use BynqIO\Dynq\Models\Project;
use Illuminate\Container\Container;

/**
 * Class BaseComponent.
 */
abstract class BaseComponent
{
    protected $container;
    protected $project;
    protected $type;
    protected $component;

    public function tabLayout(array $tabs, array $other = null)
    {
        $data['tabs'] = $tabs;
        $data = array_merge($data, $other);
        return view("component.tabs", $data);
    }

    public function response()
    {
        $data = [
            'project'   => $this->project,
            'wizard'    => $this->type, //TODO: rename
            'type'      => $this->type,
            'page'      => $this->component, //TODO: rename
            'component' => $this->component,
        ];

        return $this->render()->with($data);
    }

    public function __construct(Container $container, $project, $type, $component)
    {
        $this->container = $container;
        $this->project = $project;
        $this->type = $type;
        $this->component = $component;
    }
}