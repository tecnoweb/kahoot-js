<?php
// Includi dopo initI18n() — inietta i18n JS + carica consent.js
$consentI18n = [
    'banner_text'       => t('gdpr_banner_text'),
    'accept_all'        => t('gdpr_accept_all'),
    'necessary'         => t('gdpr_necessary'),
    'customize'         => t('gdpr_customize'),
    'save'              => t('gdpr_save'),
    'necessary_label'   => t('gdpr_necessary_label'),
    'analytics_label'   => t('gdpr_analytics_label'),
    'marketing_label'   => t('gdpr_marketing_label'),
    'necessary_desc'    => t('gdpr_necessary_desc'),
    'analytics_desc'    => t('gdpr_analytics_desc'),
    'marketing_desc'    => t('gdpr_marketing_desc'),
    'privacy_policy'    => t('privacy_policy'),
    'cookie_policy'     => t('cookie_policy'),
];
$consentJson = json_encode($consentI18n, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT);
?>
<script>window.consentI18n = <?= $consentJson ?>;</script>
<script src="/assets/consent.js" defer></script>
