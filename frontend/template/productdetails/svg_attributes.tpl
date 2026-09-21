{* Flex-Skala: Werte aus Bootstrap::flexScale() *}
{capture name="adp_flex"}
{if !empty($adpFlex)}
    <div class="adp-flex">
        <p class="adp-meter__head">
            <span class="adp-meter__label">{$oPlugin_artikel_details_plus->getLocalization()->getTranslation('artikel_details_plus_flex_title')|escape:'html'}</span>
            <span class="adp-meter__range">{$adpFlex.text|escape:'html'}</span>
        </p>
        <div class="adp-flex__segments" role="img" aria-label="{$adpFlex.text|escape:'html'}">
            {foreach $adpFlex.segments as $seg}
                <span class="adp-flex__seg{if $seg.fill >= 1} is-on{elseif $seg.fill > 0} is-half{/if}"></span>
            {/foreach}
        </div>
        <div class="adp-flex__zones">
            {foreach $adpFlex.zones as $zone}
                <span class="adp-flex__zone{if $zone.set} is-set{/if}">{$zone.label|escape:'html'}</span>
            {/foreach}
        </div>
    </div>
{/if}
{/capture}

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

    {$smarty.capture.adp_flex}
</section>
{elseif !empty($adpFlex)}
<section class="adp-panel adp-radar adp-radar--flex-only">
    {$smarty.capture.adp_flex}
</section>
{/if}

{* Körpergewicht und Fahrlevel: Werte kommen aus Bootstrap::assignDetailExtras() *}
{if !empty($adpWeight) || !empty($adpLevel)}
<section class="adp-panel adp-fit">
    {if !empty($adpWeight)}
        {assign var=adpWeightSteps value=($isMobile) ? $adpWeight.mobile : $adpWeight.desktop}
        {assign var=adpWeightTitle value=$oPlugin_artikel_details_plus->getLocalization()->getTranslation('artikel_details_plus_weight_title')}
        <div class="adp-meter adp-meter--weight">
            <p class="adp-meter__head">
                <span class="adp-meter__label">{$adpWeightTitle|escape:'html'}</span>
                <span class="adp-meter__range">{$adpWeight.from} – {$adpWeight.to} kg</span>
            </p>
            <div class="adp-meter__scale">
                {foreach $adpWeightSteps as $step}
                    <span class="adp-meter__step{if $step.set} is-set{/if}">{$step.label|escape:'html'}</span>
                {/foreach}
            </div>
        </div>
    {/if}

    {if !empty($adpLevel)}
        {assign var=adpLevelTitle value=$oPlugin_artikel_details_plus->getLocalization()->getTranslation('artikel_details_plus_level_title')}
        <div class="adp-meter adp-meter--level">
            <p class="adp-meter__head">
                <span class="adp-meter__label">{$adpLevelTitle|escape:'html'}</span>
                <span class="adp-meter__range">{if $adpLevel.from !== $adpLevel.to}{$adpLevel.from|escape:'html'} – {$adpLevel.to|escape:'html'}{else}{$adpLevel.from|escape:'html'}{/if}</span>
            </p>
            <div class="adp-meter__scale">
                {foreach $adpLevel.steps as $step}
                    <span class="adp-meter__step{if $step.set} is-set{/if}">{$step.label|escape:'html'}</span>
                {/foreach}
            </div>
        </div>
    {/if}
</section>
{/if}
