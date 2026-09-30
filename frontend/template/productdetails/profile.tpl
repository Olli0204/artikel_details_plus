{* Seitenansicht des Profils (Camber/Rocker/Flat/Hybride): Werte aus Bootstrap::buildProfileSketch() *}
{if !empty($adpProfile)}
<section class="adp-panel adp-profile adp-profile--{$adpProfile.type|replace:' ':'-'}{if $adpProfile.colored} adp-profile--zones{/if}">
    <h3 class="adp-panel__title">{$oPlugin_artikel_details_plus->getLocalization()->getTranslation('artikel_details_plus_specs_heading_profile')|escape:'html'}</h3>
    {include file='productdetails/profile_svg.tpl' p=$adpProfile}
    <p class="adp-profile__caption">
        <b>{$adpProfile.label|escape:'html'}</b>{if $adpProfile.text !== '' && $adpProfile.text|lower !== $adpProfile.label|lower} <span class="adp-profile__raw">· {$adpProfile.text|escape:'html'}</span>{/if}
    </p>
    {if !empty($adpProfile.guideUrl)}
        <p class="adp-profile__more"><a href="{$adpProfile.guideUrl|escape:'html'}">{$adpProfile.guideText|escape:'html'}</a></p>
    {/if}
</section>
{/if}
