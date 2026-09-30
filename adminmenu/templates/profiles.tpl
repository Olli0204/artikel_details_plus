{* Backend-Tab "Profil-Übersicht": welcher Begriff ergibt welche Profilskizze. Daten aus Bootstrap::renderAdminMenuTab();
   die Skizze kommt aus demselben Teil-Template wie auf der Artikelseite *}
<link rel="stylesheet" href="{$adpFrontendCss}">
<style>
    .adp-admin .adp-profile {
        --adp-accent: var(--primary, #5cbcf6);
        --adp-ink: var(--body-color, #1b1b1b);
        --adp-muted: var(--gray-600, #6c757d);
        margin: 0;
    }
    .adp-admin .adp-profile__ground { stroke: var(--border-color, #c9cfd6); }
    .adp-admin__form { max-width: 560px; }
    .adp-admin__result { max-width: 560px; margin-top: 1.25rem; }
    .adp-admin__types {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 1rem;
    }
    .adp-admin__type {
        padding: .9rem 1rem;
        border: 1px solid var(--border-color, #dee2e6);
        border-radius: 6px;
    }
    .adp-admin__type-name { margin: 0 0 .5rem; font-weight: 600; }
    .adp-admin__hints { margin: .6rem 0 0; font-size: .8rem; }
    .adp-admin__thumb { width: 240px; }
    .adp-admin__thumb .adp-profile__legend { display: none; }
    .adp-admin__examples { font-size: .78rem; }
</style>

<div class="adp-admin">
    <div class="card">
        <div class="card-header">
            <div class="subheading1">Begriff testen</div>
            <hr class="mb-n3">
        </div>
        <div class="card-body">
            <p class="text-muted">Einen Begriff eingeben, wie er in der Wawi steht (z. B. „Flying V“ oder „Camber/Rocker/Camber“). Angezeigt wird die Skizze, die die Artikelseite dazu zeichnet.</p>
            <form method="post" class="adp-admin__form">
                {$jtl_token}
                <input type="hidden" name="kPluginAdminMenu" value="{$adpMenuID}">
                <div class="input-group">
                    <input type="text" class="form-control" name="adp_profile_term" value="{$adpTerm|default:''|escape:'html'}" placeholder="Profil-Begriff" aria-label="Profil-Begriff">
                    <div class="input-group-append">
                        <button type="submit" class="btn btn-primary">Prüfen</button>
                    </div>
                </div>
            </form>
            {if $adpTerm !== null && $adpTerm !== ''}
                <div class="adp-admin__result">
                    {if $adpTermProfile}
                        <p>„{$adpTerm|escape:'html'}“ wird erkannt als <strong>{$adpTermProfile.label|escape:'html'}</strong>.</p>
                        <div class="adp-profile">{include file=$adpProfileSvgTpl p=$adpTermProfile}</div>
                    {else}
                        <div class="alert alert-warning mb-0">„{$adpTerm|escape:'html'}“ wird nicht erkannt – auf der Artikelseite erscheint dazu keine Profilskizze.</div>
                    {/if}
                </div>
            {/if}
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <div class="subheading1">Begriffe im Shop ({$adpProfileTerms|count})</div>
            <hr class="mb-n3">
        </div>
        <div class="card-body">
            <p class="text-muted">Alle Profil-Angaben an Artikeln. Reihenfolge auf der Artikelseite: Funktionsattribut <code>profil</code>, dann <code>form</code> (sonst das in der Merkmal-Zuordnung gewählte Form-Merkmal), zuletzt <code>shape</code>. Shape-Begriffe sind nur Ersatz – dass z. B. „True Twin“ kein Profil ergibt, ist normal. Nicht erkannte Profil-Begriffe stehen oben.</p>
            {if $adpProfileTerms|count > 0}
                <div class="table-responsive">
                    <table class="table table-striped table-align-top">
                        <thead>
                            <tr>
                                <th>Begriff</th>
                                <th>Quelle</th>
                                <th class="text-center">Artikel</th>
                                <th>Erkannt als</th>
                                <th>Skizze</th>
                            </tr>
                        </thead>
                        <tbody>
                            {foreach $adpProfileTerms as $row}
                                <tr>
                                    <td>
                                        <strong>{$row.term|escape:'html'}</strong>
                                        <div class="adp-admin__examples text-muted">{foreach $row.examples as $example}{$example|escape:'html'}{if !$example@last}<br>{/if}{/foreach}</div>
                                    </td>
                                    <td>{$row.label|escape:'html'}</td>
                                    <td class="text-center">{$row.count}</td>
                                    <td>
                                        {if $row.profile}
                                            {$row.profile.label|escape:'html'}
                                        {elseif $row.fallback}
                                            <span class="text-muted">– kein Profil</span>
                                        {else}
                                            <span class="badge badge-warning">nicht erkannt</span>
                                        {/if}
                                    </td>
                                    <td class="adp-admin__thumb">
                                        {if $row.profile}
                                            <div class="adp-profile">{include file=$adpProfileSvgTpl p=$row.profile}</div>
                                        {/if}
                                    </td>
                                </tr>
                            {/foreach}
                        </tbody>
                    </table>
                </div>
            {else}
                <div class="alert alert-info mb-0">Noch keine Profil-Angaben gefunden. Gepflegt werden sie als Funktionsattribut <code>form</code> bzw. <code>profil</code> oder über ein Merkmal im Tab „Merkmal-Zuordnung“.</div>
            {/if}
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <div class="subheading1">Profiltypen</div>
            <hr class="mb-n3">
        </div>
        <div class="card-body">
            <p class="text-muted">Die sechs Skizzen, die die Artikelseite zeichnen kann, mit Beispielbegriffen, die dazu führen.</p>
            <div class="adp-admin__types">
                {foreach $adpProfileTypes as $type}
                    <div class="adp-admin__type">
                        <p class="adp-admin__type-name">{$type.label|escape:'html'}</p>
                        <div class="adp-profile">{include file=$adpProfileSvgTpl p=$type}</div>
                        <p class="adp-admin__hints text-muted">Erkannt z. B. an: {foreach $type.hints as $hint}„{$hint|escape:'html'}“{if !$hint@last}, {/if}{/foreach}</p>
                    </div>
                {/foreach}
            </div>
        </div>
    </div>
</div>
