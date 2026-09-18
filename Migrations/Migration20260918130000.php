<?php declare(strict_types=1);

namespace Plugin\artikel_details_plus\Migrations;

use JTL\Plugin\Migration;
use JTL\Update\IMigration;

/**
 * 0.3.0: Der Countdown ist in die Countdown-Verwaltung von Startseite Plus umgezogen.
 * Die alten Einstellungswerte werden entfernt (die Definitionen kommen ohnehin aus info.xml).
 */
class Migration20260918130000 extends Migration implements IMigration
{
    public function up(): void
    {
        $this->execute(
            "DELETE FROM tplugineinstellungen
             WHERE cName IN ('artikel_details_plus_countdown_aktiv', 'artikel_details_plus_countdown_date', 'artikel_details_plus_countdown_time')"
        );
    }

    public function down(): void
    {
    }
}
