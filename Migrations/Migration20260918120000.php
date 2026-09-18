<?php declare(strict_types=1);

namespace Plugin\artikel_details_plus\Migrations;

use JTL\Plugin\Migration;
use JTL\Update\IMigration;

/**
 * Ab 0.2.2 sind alle Aktiv-Schalter wieder Checkboxen. JTL speichert dafuer 'on' (angehakt)
 * bzw. '' (abgewaehlt). Die Selectboxen aus 0.1.1 bis 0.2.1 haben 'Y'/'N' gespeichert.
 */
class Migration20260918120000 extends Migration implements IMigration
{
    private const SETTINGS = "(
                 'artikel_details_plus_merkmalwerte_aktiv',
                 'artikel_details_plus_fahrlevel_aktiv',
                 'artikel_details_plus_specs_characteristics_aktiv',
                 'artikel_details_plus_specs_dimensions_aktiv',
                 'artikel_details_plus_merkmalbilder_aktiv',
                 'artikel_details_plus_countdown_aktiv',
                 'artikel_details_plus_lagerbestand_aktiv',
                 'artikel_details_plus_cheaper_aktiv'
             )";

    public function up(): void
    {
        $this->execute("UPDATE tplugineinstellungen SET cWert = 'on' WHERE cName IN " . self::SETTINGS . " AND cWert = 'Y'");
        $this->execute("UPDATE tplugineinstellungen SET cWert = '' WHERE cName IN " . self::SETTINGS . " AND cWert = 'N'");
    }

    public function down(): void
    {
        $this->execute("UPDATE tplugineinstellungen SET cWert = 'Y' WHERE cName IN " . self::SETTINGS . " AND cWert = 'on'");
        $this->execute("UPDATE tplugineinstellungen SET cWert = 'N' WHERE cName IN " . self::SETTINGS . " AND cWert = ''");
    }
}
