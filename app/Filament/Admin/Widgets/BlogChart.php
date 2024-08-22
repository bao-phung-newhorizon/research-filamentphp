<?php

namespace App\Filament\Admin\Widgets;

use App\Models\User;
use Filament\Widgets\ChartWidget;

class BlogChart extends ChartWidget
{
    protected static ?string $heading = 'Chart';

    protected function getData(): array
    {
        $users = User::withCount('blogs')->get(['name', 'blogs_count']);

        $userData = $users->map(function ($user) {
            return [
                'name' => $user->name,
                'blogs' => $user->blogs_count,
            ];
        });

        return [
            'datasets' => [
                [
                    'label' => 'Users',
                    'data' => $userData->pluck('blogs')->all(),
                ],
            ],
            'labels' => $userData->pluck('name')->all(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
