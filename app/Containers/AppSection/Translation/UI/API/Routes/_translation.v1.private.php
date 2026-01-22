<?php

/**
 * @apiDefine TranslationLocalesResponse
 *
 * @apiSuccess {Object} data
 * @apiSuccess {String} data.type
 * @apiSuccess {String} data.id
 * @apiSuccess {Object[]} data.installed
 * @apiSuccess {Object[]} data.available
 */

/**
 * @apiDefine TranslationLocaleStatusResponse
 *
 * @apiSuccess {Object} data
 * @apiSuccess {String} data.type
 * @apiSuccess {String} data.id
 * @apiSuccess {String} data.locale
 * @apiSuccess {Boolean} data.downloaded
 * @apiSuccess {Boolean} data.copied
 */

/**
 * @apiDefine TranslationGroupListResponse
 *
 * @apiSuccess {Object} data
 * @apiSuccess {String} data.type
 * @apiSuccess {String} data.id
 * @apiSuccess {String} data.locale
 * @apiSuccess {String[]} data.groups
 */

/**
 * @apiDefine TranslationGroupResponse
 *
 * @apiSuccess {Object} data
 * @apiSuccess {String} data.type
 * @apiSuccess {String} data.id
 * @apiSuccess {String} data.locale
 * @apiSuccess {String} data.group
 * @apiSuccess {Object} data.translations
 */
