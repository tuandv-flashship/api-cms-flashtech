<?php

/**
 * @apiDefine GeneralSettingsResponse
 *
 * @apiSuccess {Object} data
 * @apiSuccess {String} data.object
 * @apiSuccess {String} data.id
 * @apiSuccess {String[]} data.admin_email
 * @apiSuccess {String} data.time_zone
 * @apiSuccess {Boolean} data.enable_send_error_reporting_via_email
 * @apiSuccess {String} data.locale
 */

/**
 * @apiDefine PhoneNumberSettingsResponse
 *
 * @apiSuccess {Object} data
 * @apiSuccess {String} data.object
 * @apiSuccess {String} data.id
 * @apiSuccess {Boolean} data.phone_number_enable_country_code
 * @apiSuccess {String[]} data.phone_number_available_countries
 * @apiSuccess {Number} data.phone_number_min_length
 * @apiSuccess {Number} data.phone_number_max_length
 */
