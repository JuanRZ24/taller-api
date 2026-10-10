<?php

namespace App\Services\Module;

use App\Models\Course;
use App\Models\Module;
use Illuminate\Support\Facades\DB;

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

    /**
     * @param  array<int, int>  $ids
     */
    public function reorder(array $ids): void
    {
        DB::transaction(function () use ($ids) {
            foreach ($ids as $i => $id) {
                Module::whereKey($id)->update(['position' => $i + 1]);
            }
        });
    }
}