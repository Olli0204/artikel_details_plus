<?php declare(strict_types=1);
/**
 * dynamic data source for the "Merkmal-Zuordnung" selectboxes: all article features
 *
 * return value of these functions has to be an array of objects
 * where every object should have the members cWert, cName and optional nSort
 *
 * @package artikel_details_plus
 */

$options = [(object)['cWert' => '0', 'cName' => '– kein Merkmal –', 'nSort' => 0]];
foreach (
    \JTL\Shop::Container()->getDB()->getObjects('SELECT kMerkmal AS cWert, cName FROM tmerkmal ORDER BY cName, kMerkmal')
    as $idx => $feature
) {
    $feature->nSort = $idx + 1;
    $options[]      = $feature;
}

return $options;
