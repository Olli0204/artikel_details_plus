{* Flex-Skala: Werte aus Bootstrap::flexScale() *}
{if !empty($adpFlex)}
<section class="adp-panel adp-flex">
    <h3 class="adp-panel__title">{$oPlugin_artikel_details_plus->getLocalization()->getTranslation('artikel_details_plus_flex_title')|escape:'html'}</h3>
    <p class="adp-meter__head">
        <span class="adp-flex__zone-text">{$adpFlex.zone|escape:'html'}</span>
        <span class="adp-flex__value">{$adpFlex.value|escape:'html'}</span>
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
</section>
{/if}
