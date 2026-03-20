<?php

use App\Models\Award;
use App\Models\Basic;
use App\Models\Certificate;
use App\Models\Education;
use App\Models\Interest;
use App\Models\Language;
use App\Models\Project;
use App\Models\Publication;
use App\Models\Reference;
use App\Models\Skill;
use App\Models\User;
use App\Models\Volunteer;
use App\Models\Work;
use Illuminate\Contracts\Console\Kernel;

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';

$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

$models = [
    Award::class,
    Basic::class,
    Certificate::class,
    Education::class,
    Interest::class,
    Language::class,
    Project::class,
    Publication::class,
    Reference::class,
    Skill::class,
    Volunteer::class,
    Work::class,
    User::class,
];

foreach ($models as $model) {
    if (! class_exists($model)) {
        echo "Model not found: {$model}\n";

        continue;
    }

    try {
        $count = $model::count();
    } catch (Throwable $e) {
        echo "Error counting {$model}: {$e->getMessage()}\n";

        continue;
    }

    echo "---- {$model} ----\n";
    echo "count: {$count}\n";

    if ($count > 0) {
        $first = $model::query()->first();
        if ($first) {
            $arr = method_exists($first, 'toArray') ? $first->toArray() : (array) $first;
            echo json_encode($arr, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)."\n";
        }
    }

    echo "\n";
}

echo "Script finished.\n";
