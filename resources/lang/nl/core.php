<?php

/**
 * Copyright (C) 2017 Bynq.io B.V.
 * All Rights Reserved
 *
 * This file is part of the BynqIO\CalculatieTool.com.
 *
 * Content can not be copied and/or distributed without the express
 * permission of the author.
 *
 * @package  CalculatieTool
 * @author   Yorick de Wid <y.dewid@calculatietool.com>
 */

return [

    /*
    |--------------------------------------------------------------------------
    | Core Language Lines
    |--------------------------------------------------------------------------
    |
    | 
    |
    */

    // Generic
    'yes'         => 'Ja',
    'no'          => 'Nee',
    'new'         => 'Nieuw',
    'open'        => 'Open',
    'closed'      => 'Gesloten',
    'name'        => 'Naam',
    'type'        => 'Type',
    'city'        => 'Plaats',

    // Authentication
    'blocked'          => 'Toegang geblokkeerd voor 15 minuten. Probeer later opnieuw',
    'notconfirmed'     => 'Email nog niet bevestigd',
    'authfailed'       => 'Gebruikersnaam en/of wachtwoord verkeerd',
    'invactivatelink'  => 'Activatielink is niet geldig',
    'accoutncreated'   => 'Account aangemaakt, er is een bevestingsmail verstuurd',
    'passresetsent'    => 'Wachtwoord herstel instructies zijn verzonden',
    'invresetlink'     => 'Link is ongeldig',
    'passresetok'      => 'Wachtwoord is aangepast',

    // Dashboard
    'dashboard'       => 'Dashboard',
    'mobilewarning'   => 'De applicatie werkt het beste op desktop of tablet',
    'needhelp'        => 'Hulp gewenst?',
    'companyinfo'     => 'Bedrijfsgegevens',
    'products'        => 'Producten',
    'timesheet'       => 'Urenregistratie',
    'purchaseinvoice' => 'Inkoopfacturen',
    'financial'       => 'Financieel',
    'relation'        => 'Relatie|Relaties',
    'project'         => 'Project|Projecten',
    'projectname'     => 'Projectnaam',
    'customer'        => 'Opdrachtgever',
    'firststep'       => 'De eerste stap...',
    'watchwelcvid'    => 'Bekijk de Welkomstvideo',
    'noprodavail'     => 'Geen projecten beschikbaar',
    'crefirstprod'    => 'Maak eerste project aan',
    'recent_projects' => 'Recente projecten',

    // Daypart welcome
    'welcome' => [
        'morning'   => 'Goedemorgen',
        'afternoon' => 'Goedenmiddag',
        'evening'   => 'Goedenavond',
        'night'     => 'Goedenacht',
    ],

    // Account
    'account' => 'Account',

    // Support
    'support' => 'Support',

    // Components
    'components' => [
        'last_updated' => 'Laatst bijgewerkt :timestamp',
        'status'       => [
            0 => 'Onbekend',
            1 => 'Operationeel',
            2 => 'Prestatieproblemen',
            3 => 'Gedeeltelijke Storing',
            4 => 'Grote Storing',
        ],
        'group' => [
            'other' => 'Andere componenten',
        ],
    ],

    // Incidents
    'incidents' => [
        'none'          => 'Geen incidenten gemeld',
        'past'          => 'Oude incidenten',
        'previous_week' => 'Vorige week',
        'next_week'     => 'Volgende week',
        'stickied'      => 'Vastgezette incidenten',
        'scheduled'     => 'Gepland onderhoud',
        'scheduled_at'  => ', gepland :timestamp',
        'posted'        => 'Geplaatst op :timestamp',
        'status'        => [
            1 => 'In onderzoek',
            2 => 'Geïdentificeerd',
            3 => 'Aan het opvolgen',
            4 => 'Opgelost',
        ],
    ],

    // Schedule
    'schedules' => [
        'status' => [
            0 => 'Aankomend',
            1 => 'Wordt uitgevoerd',
            2 => 'Voltooid',
        ],
    ],

    // Service Status
    'service' => [
        'good'  => '[0,1] Systeem operationeel|[2,Inf] Alle systemen zijn operationeel',
        'bad'   => '[0,1] Het systeem ondervind momenteel problemen |[2,Inf] Sommige systemen ondervinden momenteel problemen',
        'major' => '[0,1] De service ondervind momenteel a grote storing|[2,Inf] Sommige systemen ondervinden momenteel een grote storing',
    ],

    'api' => [
        'regenerate' => 'Hergenereer API-sleutel',
        'revoke'     => 'API-sleutel Intrekken',
    ],

    // Metrics
    'metrics' => [
        'filter' => [
            'last_hour' => 'Laatste uur',
            'hourly'    => 'Laatste 12 uur',
            'weekly'    => 'Wekelijks',
            'monthly'   => 'Maandelijks',
        ],
    ],

    // Subscriber
    'subscriber' => [
        'subscribe'   => 'Abonneer voor de meest recente updates',
        'unsubscribe' => 'Unsubscribe at :link',
        'button'      => 'Abonneren',
        'manage'      => [
            'no_subscriptions' => 'Je bent momenteel geabonneerd op alle updates.',
            'my_subscriptions' => 'Je bent momenteel geabonneerd op de volgende updates.',
        ],
        'email' => [
            'subscribe'          => 'Abonneren op e-mail updates.',
            'subscribed'         => 'U bent geabonneerd op e-mail notificaties, controleer uw e-mail om uw abonnement te bevestigen.',
            'verified'           => 'Uw e-mail abonnement is bevestigd. Bedankt!',
            'manage'             => 'Beheer je abonnement',
            'unsubscribe'        => 'Afmelden voor e-mail updates.',
            'unsubscribed'       => 'Uw e-mail abonnement is opgezegd.',
            'failure'            => 'Er ging iets fout tijdens het aanmelden.',
            'already-subscribed' => 'Kan niet aanmelden voor :email omdat deze momenteel al is aangemeld.',
        ],
    ],

    'signup' => [
        'title'    => 'Registreer',
        'username' => 'Gebruikersnaam',
        'email'    => 'E-mail',
        'password' => 'Wachtwoord',
        'success'  => 'Uw account is aangemaakt.',
        'failure'  => 'Er is iets misgegaan met het inschrijven.',
    ],

    'system' => [
        'update' => 'Er is een nieuwere versie van Cachet beschikbaar. Kijk hoe je moet updaten <a href="https://docs.cachethq.io/docs/updating-cachet">hier</a>!',
    ],

    // Modal
    'modal' => [
        'close'     => 'Sluiten',
        'subscribe' => [
            'title'  => 'Abonneren voor component updates',
            'body'   => 'Vul uw e-mailadres in om updates te krijgen voor dit component. Als u al ingeschreven bent, krijgt u al emails voor dit component.',
            'button' => 'Abonneren',
        ],
    ],

    // Other
    'description'     => 'Blijf op de hoogte van de laatste service updates over :app.',
    'powered_by'      => 'Mogelijk gemaakt door <a href="https://cachethq.io" class="links">Cachet</a>.',
    'timezone'        => 'Tijden worden getoond in :timezone.',
    'about_this_site' => 'Over deze website',
    'rss-feed'        => 'RSS',
    'atom-feed'       => 'Atom',
    'feed'            => 'Status Feed',

];
