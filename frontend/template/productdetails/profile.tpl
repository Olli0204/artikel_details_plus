{* Seitenansicht des Profils (Camber/Rocker/Flat/Hybride): Werte aus Bootstrap::buildProfileSketch() *}
{if !empty($adpProfile)}
<section class="adp-panel adp-profile adp-profile--{$adpProfile.type|replace:' ':'-'}">
    <h3 class="adp-panel__title">{$oPlugin_artikel_details_plus->getLocalization()->getTranslation('artikel_details_plus_specs_heading_profile')|escape:'html'}</h3>
    <div class="adp-profile__sketch">
        <svg class="adp-profile__svg" viewBox="{$adpProfile.viewBox}" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="{$adpProfile.label|escape:'html'}">
            <line class="adp-profile__ground" x1="8" y1="{$adpProfile.ground}" x2="592" y2="{$adpProfile.ground}"/>
            <path class="adp-profile__board" d="{$adpProfile.path}"/>
        </svg>
    </div>
    <p class="adp-profile__caption">
        <b>{$adpProfile.label|escape:'html'}</b>{if $adpProfile.text !== '' && $adpProfile.text|lower !== $adpProfile.label|lower} <span class="adp-profile__raw">· {$adpProfile.text|escape:'html'}</span>{/if}
    </p>
</section>
{/if}
