{* Fahreigenschaften: Werte kommen aus Bootstrap::assignSnowboardSpecs() (Funktionsattribute mit Vater-Fallback) *}
{if !empty($adpSpecsCharacteristics)}
<section class="adp-panel adp-radar">
    <h3 class="adp-panel__title">{$oPlugin_artikel_details_plus->getLocalization()->getTranslation('artikel_details_plus_specs_heading_characteristics')|escape:'html'}</h3>

    <div class="adp-radar__chart"></div>

    <ul class="adp-radar__legend">
        {foreach $adpSpecsCharacteristics as $c}
            <li class="adp-radar__chip" data-adp-radar-index="{$c@index}">{$c.label|escape:'html'} <b>{$c.value}</b>/{$c.max}</li>
        {/foreach}
    </ul>

    <script src="{$adpFrontendURL}js/ecm_polygon_svg.js?v={$oPlugin_artikel_details_plus->getMeta()->getVersion()}"></script>
    <script>
        $(function () {
            // [label, description, value, max_value]
            var data = [{foreach $adpSpecsCharacteristics as $c}['{$c.label|escape:'javascript'}', '', {$c.value}, {$c.max}]{if !$c@last}, {/if}{/foreach}];

            $('.adp-radar__chart').each(function () {
                var chart = $(this);
                if (chart.children().length) {
                    return; // Diagramm wurde bereits gezeichnet
                }
                chart.append(new ECM_POLYGON_SVG(500, 400, 'black', 'red', 5, data).getHTML());

                var chips = chart.closest('.adp-radar').find('.adp-radar__chip');
                chart.find('.ecm_button').each(function (index) {
                    $(this).on('mouseenter focusin', function () {
                        chips.removeClass('is-active').filter('[data-adp-radar-index="' + index + '"]').addClass('is-active');
                    }).on('mouseleave focusout', function () {
                        chips.removeClass('is-active');
                    });
                });
            });
        });
    </script>

</section>
{/if}
