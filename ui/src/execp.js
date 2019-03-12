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

export default function execp(argv, indata = null) {
    var nethserver = window.nethserver
    return new Promise(function(resolve, reject) {
        nethserver.exec(typeof argv === 'string' ? [argv] : argv, indata, null, resolve, reject);
    })
    .then(function(output) {
        return typeof output === 'string' ? JSON.parse(output) : output
    })
}
