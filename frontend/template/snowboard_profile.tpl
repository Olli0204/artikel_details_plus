{* Erklärseite "Snowboard-Profile" (FrontendLink adpProfileGuide): Daten aus Bootstrap::prepareFrontend(),
   Skizzen aus demselben Teil-Template wie auf der Artikelseite *}
{if !empty($adpGuide)}
<link rel="stylesheet" href="{$adpFrontendCss}">
<div class="container">
    <div class="adp-guide">
        <p class="adp-guide__intro">{$adpGuide.intro|escape:'html'}</p>

        {if $adpGuide.colored}
            <section class="adp-panel adp-guide__legend">
                <h2 class="adp-panel__title">{$adpGuide.legendTitle|escape:'html'}</h2>
                <ul class="adp-guide__zones">
                    {foreach $adpGuide.zones as $zone}
                        <li class="adp-guide__zone adp-profile">
                            <span class="adp-profile__dot adp-profile__dot--{$zone.kind}"></span>
                            <span><b>{$zone.label|escape:'html'}</b> – {$zone.text|escape:'html'}</span>
                        </li>
                    {/foreach}
                </ul>
            </section>
        {/if}

        <div class="adp-guide__grid">
            {foreach $adpGuide.profiles as $p}
                <section class="adp-panel adp-guide__profile adp-profile" id="{$p.anchor}">
                    <h2 class="adp-guide__name">{$p.label|escape:'html'}</h2>
                    {include file=$adpProfileSvgTpl p=$p}
                    <p class="adp-guide__text">{$p.text|escape:'html'}</p>
                    <dl class="adp-guide__facts">
                        <dt>{$adpGuide.feelLabel|escape:'html'}</dt>
                        <dd>{$p.feel|escape:'html'}</dd>
                        <dt>{$adpGuide.suitedLabel|escape:'html'}</dt>
                        <dd>{$p.suited|escape:'html'}</dd>
                    </dl>
                    {if !empty($p.aka)}
                        <p class="adp-guide__aka">
                            <span class="adp-guide__aka-label">{$adpGuide.akaLabel|escape:'html'}:</span>
                            {foreach $p.aka as $name}<span class="adp-guide__chip">{$name|escape:'html'}</span>{/foreach}
                        </p>
                    {/if}
                </section>
            {/foreach}
        </div>
    </div>
</div>
{/if}
