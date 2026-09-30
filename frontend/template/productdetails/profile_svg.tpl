{* Seitenansicht + Legende eines Profils aus Bootstrap::buildProfileSketch(); $p = Profil-Array.
   Gemeinsam genutzt von profile.tpl (Artikelseite) und dem Backend-Tab "Profil-Übersicht" *}
<div class="adp-profile__sketch">
    <svg class="adp-profile__svg" viewBox="{$p.viewBox}" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="{$p.label|escape:'html'}">
        <line class="adp-profile__ground" x1="8" y1="{$p.ground}" x2="592" y2="{$p.ground}"/>
        {if $p.colored}
            {foreach $p.zones as $zone}
                <path class="adp-profile__board adp-profile__seg adp-profile__seg--{$zone.kind}" d="{$zone.path}"/>
            {/foreach}
        {else}
            <path class="adp-profile__board" d="{$p.path}"/>
        {/if}
    </svg>
</div>
{if $p.colored}
    <ul class="adp-profile__legend">
        {foreach $p.legend as $item}
            <li class="adp-profile__legend-item"><span class="adp-profile__dot adp-profile__dot--{$item.kind}"></span>{$item.label|escape:'html'}</li>
        {/foreach}
    </ul>
{/if}
