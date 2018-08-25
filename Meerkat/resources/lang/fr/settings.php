<?php

return [

    'publishing' => 'Paramètres de publication',
    'publishing_instruct' => 'Ces paramètres vous permettent de configurer comment les nouveaux commentaires sont traités ainsi que certains paramètres d\'affichage.',

    'auto_publish' => 'Publier les commentaires automatiquement',
    'auto_publish_instruct' => 'Décocher cette case pour passer en revue vos commentaires avant de les rendre publics.',

    'auto_publish_authenticated_users' => 'Auto Publish Authenticated User Comments',
    'auto_publish_authenticated_users_instruct' => 'Quand cette option est activée, les commentaires créés par les utilisateurs connectés sont approuvés automatiquement.',

    'cp_avatar_driver' => 'Avatars des auteurs de commentaires',
    'cp_avatar_driver_instruct' => 'Quelle méthode voulez-vous utiliser pour afficher les avatars des auteurs sur votre site?',

    'security' => 'Sécurité',
    'security_instruct' => 'Ces paramètres vous permettent de sécuriser la réputation de votre site en configurant les règles de sécurité et de filtrage des indésirables.',

    'remove_spam_after' => 'Supprimer automatiquement les commentaires après',
    'remove_spam_after_instruct' => 'La fréquence à laquelle les commentaires indésirables doivent être supprimés automatiquement.',

    'auto_check_spam' => 'Filtrer automatiquement les indésirables parmis les nouveaux commentaires',
    'auto_check_spam_instruct' => 'Quand cette option est activées, les nouveaux commentaires seront automatiquement filtrés. Si vous publiez automatiquement les commentaires des auteurs connectés sur votre site, ceux-ci ne seront pas filtrés.',

    'auto_delete_spam' => 'Supprimer automatiquement les commentaires marqués comme indésirables',
    'auto_delete_spam_instruct' => 'Supprimer automatiquement les commentaires marqués comme indésirables. Attention! Activer cette option ne vous laissera pas la possibilité de passer en revue les commentaires marqués comme indésirables par erreur.',

    'akismet_api_key' => 'Clef d\'API Akismet',
    'akismet_api_key_instruct' => 'Si vous voulez utiliser le service de filtrage Akismet, saisissez votre clef d\'API Askimet ici..',

    'akismet_front_page' => 'Page d\'accueil Akismet',
    'akismet_front_page_instruct' => 'Pour configurer la page d\'accueil (ou l\'URL du blog) de votre site, saisissez une valeur ici. Si vous ne saissiez rien, le système utilisera l\'URL du site Statamic.',

    'auto_submit_results' => 'Transmettre les résultats du filtrage des indésirables',
    'auto_submit_results_instruct' => 'Meerkat transmettra les commentaires marqués comme indésirables à votre service de filtrage; Meerkat soumettra aussi les commentaires marqués comme désirables afin de permettre au prestatire d\'améliorer leur service de détection. Cela signifie que certaines informations issues de votre site seront transmise à un fournisseur tiers.',

    'automatically_close_comments' => 'Désactiver les commentaires automatiquement',
    'automatically_close_comments_instruct' => 'Après combien de publications les commentaires doivent-ils être désactivés sur un article? Saisir "0" pour permettre les commentaires indéfiniement.',

    'license' => 'Licence Meerkat',
    'license_key' => 'Clef de licence Meerkat',

    'license_instruct' => 'Afin de pouvoir utiliser Meerkat sur un site public, vous devez saisir une clef de licence.',
    'license_key_instruct' => 'Saisissez la clef de licence pour ce domaine sur votre <a href="https://bag.stillat.com/licenses" target="_blank">compte Stillat</a>.',
    'license_submit' => 'Enregistrer la licence',
];
