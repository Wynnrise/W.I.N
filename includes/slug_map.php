<?php

/**
 * includes/slug_map.php
 *
 * Resolves FLAG 1: plex_properties.neighbourhood_slug stores 'nb_012' format.
 * All other tables (neighbourhoods, monthly_market_stats, construction_costs,
 * cmhc_benchmarks, wynston_outlook) use 'renfrew-collingwood' format.
 *
 * This file is the single source of truth for that mapping.
 * Include it wherever a slug needs to be resolved.
 *
 * Usage:
 *   require_once __DIR__ . '/../includes/slug_map.php';
 *   $slug = wynston_resolve_slug($property['neighbourhood_slug']);
 *   // $slug is now 'renfrew-collingwood' regardless of input format
 *
 * When the nightly cron is updated to write the correct slug format directly
 * into plex_properties, this file can be retired — all calls will just
 * pass through unchanged.
 */


/**
 * Maps COV neighbourhood codes ('nb_012') to human slugs ('renfrew-collingwood').
 * Source: City of Vancouver neighbourhood code list cross-referenced with COV Open Data.
 */
const WYNSTON_SLUG_MAP = [

    // ── East Vancouver ───────────────────────────────────────
    'nb_001' => 'arbutus-ridge',
    'nb_002' => 'downtown',
    'nb_003' => 'dunbar-southlands',
    'nb_004' => 'fairview',
    'nb_005' => 'false-creek',
    'nb_006' => 'grandview-woodland',
    'nb_007' => 'hastings-sunrise',
    'nb_008' => 'kensington-cedar-cottage',
    'nb_009' => 'kerrisdale',
    'nb_010' => 'killarney',
    'nb_011' => 'kitsilano',
    'nb_012' => 'marpole',
    'nb_013' => 'mount-pleasant',
    'nb_014' => 'oakridge',
    'nb_015' => 'renfrew-collingwood',
    'nb_016' => 'riley-park',
    'nb_017' => 'shaughnessy',
    'nb_018' => 'south-cambie',
    'nb_019' => 'strathcona',
    'nb_020' => 'sunset',
    'nb_021' => 'victoria-fraserview',
    'nb_022' => 'west-end',
    'nb_023' => 'west-point-grey',

    // ── Additional COV codes (confirm against live API response) ──
    // These codes appear in COV Open Data — add more as discovered
    'nb_024' => 'musqueam',
    'nb_025' => 'south-marine',
    'nb_026' => 'fraser-ve',
    'nb_027' => 'knight',
    'nb_028' => 'main',
    'nb_029' => 'fairview-vw',
    'nb_030' => 'west-end-vw',
];


/**
 * Resolve a neighbourhood slug to the human-readable format.
 *
 * Accepts both formats:
 *   'nb_015'              → 'renfrew-collingwood'
 *   'renfrew-collingwood' → 'renfrew-collingwood'  (pass-through)
 *   'unknown_code'        → 'metro-vancouver'       (safe fallback)
 *
 * @param  string|null $raw_slug  Value from plex_properties.neighbourhood_slug
 * @return string                 Human slug for DB joins
 */
function wynston_resolve_slug(?string $raw_slug): string {

    if (empty($raw_slug)) {
        return 'metro-vancouver';
    }

    // Already in human format — pass through
    if (!str_starts_with($raw_slug, 'nb_')) {
        return $raw_slug;
    }

    // Map nb_XXX → human slug
    return WYNSTON_SLUG_MAP[$raw_slug] ?? 'metro-vancouver';
}


/**
 * Reverse lookup: human slug → nb_XXX code.
 * Used by the nightly cron when updating plex_properties.
 *
 * @param  string $human_slug   e.g. 'renfrew-collingwood'
 * @return string|null          e.g. 'nb_015', or null if not found
 */
function wynston_slug_to_code(string $human_slug): ?string {
    $flipped = array_flip(WYNSTON_SLUG_MAP);
    return $flipped[$human_slug] ?? null;
}
