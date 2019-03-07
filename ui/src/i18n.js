/*
 * Copyright (C) 2019 Nethesis S.r.l.
 * http://www.nethesis.it - nethserver@nethesis.it
 *
 * This script is part of NethServer.
 *
 * NethServer is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License,
 * or any later version.
 *
 * NethServer is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with NethServer.  If not, see COPYING.
 */

import axios from "axios"

var console = window.console

export default function (i18n) {

    var locale = window.localStorage.getItem('cockpit.lang')
    if(locale && locale != 'en') {
        locale = locale.substr(0,2)
    } else {
        // nothing to do: use default settings
        return i18n
    }

    // The Cockpit server fallbacks to language.json if the requested locale
    // is not available:
    axios.get(`./i18n/language.${locale}.json`)
    .then(function(response) {
        i18n.setLocaleMessage(locale, Object.assign(i18n.messages, response.data))
        i18n.locale = locale
    })
    .catch(function(error) {
        console.error('JSON language HTTP request failed', error)
    });

    return i18n
}
