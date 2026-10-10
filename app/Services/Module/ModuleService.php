<?php

namespace App\Services\Module;

use App\Models\Course;
use App\Models\Module;

class ModuleService
{
    public function store(Course $course, array $data): Module
    {
        $module = new Module($data);
        $module->position = ($course->modules()->max('position') ?? 0) + 1;

        $course->modules()->save($module);

        return $module;
    }

    public function update(Module $module, array $data): Module
    {
        $module->update($data);

        return $module;
    }

    public function destroy(Module $module): void
    {
        $module->delete();
    }
}