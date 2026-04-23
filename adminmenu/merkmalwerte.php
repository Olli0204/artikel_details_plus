<?php declare(strict_types=1);
/**
 * dynamic data source for selectbox with article features
 *
 * return value of these functions has to be an array of objects
 * where every object should have the members cWert, cName and optional nSort
 *
 * @package artikel_details_plus
 */

return \JTL\Shop::Container()->getDB()->query(
    'SELECT DISTINCT tm.kMerkmal AS cWert, cName 
    FROM tmerkmal tm 
    INNER JOIN tmerkmalwert tmw ON tm.kMerkmal = tmw.kMerkmal 
    WHERE tmw.cBildpfad IS NOT NULL AND tmw.cBildpfad <> "" ',
    \JTL\DB\ReturnType::ARRAY_OF_OBJECTS
);