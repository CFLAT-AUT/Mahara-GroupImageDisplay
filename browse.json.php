<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2009 Catalyst IT Ltd and others; see:
 *                         http://wiki.mahara.org/Contributors
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    mahara
 * @subpackage artefact-browse
 * @author     Mike Kelly UAL m.f.kelly@arts.ac.uk / Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 *
 */

define('INTERNAL', 1);
define('JSON', 1);

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
//safe_require('artefact', 'browse');
safe_require('blocktype', 'groupviewsimage');

$limit = param_integer('limit', 20);
$offset = param_integer('offset', 0);
/*
$keywordtype = param_alpha('searchtype', 'user');

$filters = array();

if ($keyword = param_variable('keyword', '')) {
    $filters['keyword'] = $keyword;
    $filters['keywordtype'] = $keywordtype;
}
if ($college = param_variable('college', '')) {
    $filters['college'] = $college;
}
if ($course = param_variable('course', '')) {
    $filters['course'] = $course;
}
*/

//20140627 JW commented out above segment as it is not required for groupviewsimage
//We are only interested in groupid, offset and limit

$groupid = param_variable('groupid', '');

$items = PluginBlocktypeGroupViewsImage::get_data($groupid, $offset, $limit);
PluginBlocktypeGroupViewsImage::build_browse_list_html($items);

//$items = ArtefactTypeBrowse::get_browsable_items($filters, $offset, $limit);
//ArtefactTypeBrowse::build_browse_list_html($items);

json_reply(false, (object) array('message' => false, 'data' => $items));
