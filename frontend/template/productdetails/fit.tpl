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
