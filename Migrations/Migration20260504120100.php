<?php declare(strict_types=1);

namespace Plugin\artikel_details_plus\Migrations;

use JTL\Plugin\Migration;
use JTL\Update\IMigration;

class Migration20260504120100 extends Migration implements IMigration
{
    public function up(): void
    {
        $this->execute(
            "UPDATE tplugineinstellungen
             SET cWert = 'Y'
             WHERE cName IN (
                 'artikel_details_plus_merkmalwerte_aktiv',
                 'artikel_details_plus_fahrlevel_aktiv',
                 'artikel_details_plus_merkmalbilder_aktiv',
                 'artikel_details_plus_countdown_aktiv',
                 'artikel_details_plus_lagerbestand_aktiv',
                 'artikel_details_plus_cheaper_aktiv'
             ) AND cWert = 'on'"
        );
    }

    public function down(): void
    {
        $this->execute(
            "UPDATE tplugineinstellungen
             SET cWert = 'on'
             WHERE cName IN (
                 'artikel_details_plus_merkmalwerte_aktiv',
                 'artikel_details_plus_fahrlevel_aktiv',
                 'artikel_details_plus_merkmalbilder_aktiv',
                 'artikel_details_plus_countdown_aktiv',
                 'artikel_details_plus_lagerbestand_aktiv',
                 'artikel_details_plus_cheaper_aktiv'
             ) AND cWert = 'Y'"
        );
    }
}
