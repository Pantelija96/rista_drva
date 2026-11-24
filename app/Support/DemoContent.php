<?php

namespace App\Support;

final class DemoContent
{
    // Explicitly illustrative upcoming activities retained from the approved frontend.
    public static function events(): array
    {
        return [
            [
                'title' => __('public.autumn_planting_in_the_neighborhood'),
                'city' => 'Beograd',
                'description' => __('public.planting_together_and_learning_about_caring_for_young_trees'),
                'photo' => 'planting',
            ],
            [
                'title' => __('public.one_generation_s_green_footprint'),
                'city' => 'Jagodina',
                'description' => __('public.a_workshop_and_symbolic_planting_with_the_school_community'),
                'photo' => 'seedlings',
            ],
            [
                'title' => __('public.small_steps_great_canopies'),
                'city' => 'Gornji Milanovac',
                'description' => __('public.learning_about_saplings_and_preparing_for_the_next_event'),
                'photo' => 'gardening',
            ],
        ];
    }
}
