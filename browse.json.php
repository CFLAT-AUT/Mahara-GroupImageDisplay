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

$data = PluginBlocktypeGroupViewsImage::get_data($filters, $offset, $limit);

//the array that is returned contains a few other things but we just wanted to focus on sharedviews
		//the foreach below will loop through all views shared with the group
		foreach($data[sharedviews] as $aView){
			//the foreach below will loop through all the properties for a view (returned by get_data method) and assigns them to the required variables
			foreach($aView as $aViewProperty){
				//get the view
				$viewID = $aViewProperty[id]; //the page shared
				$fullurl = $aViewProperty[fullurl]; //full url of the page shared
				$viewTitle = str_shorten_text($aViewProperty[displaytitle], $texttitletrim, true); //view's title
				
				//get the owner of the view
				$viewOwnerName = $aViewProperty[user]->firstname." ".$aViewProperty[user]->lastname; //owner of the view's name
				$userobj = new User();
				$userobj->find_by_id($aViewProperty[user]->id);
				$profileurl = profile_url($userobj); //owner of the view's proflie page
				$avatarurl = profile_icon_url($aViewProperty[user]->id,50,50); //owner of the view's profile picture
				
				
				//get the artefact id of an image in the view
				$theView = new View($aViewProperty[id]); //create the view
				$artefactInView = $theView->get_artefact_metadata(); //get all artefacts in the view
				foreach($artefactInView as $anArtefact){ //for each artefact
					if($anArtefact->artefacttype == 'image'){
						$artefactID = $anArtefact->id; //if it is an image artefact assign the id and break the loop
						break;
					}
				}
				
				//the items variable below requires the contents array to be in this format
				$contents['photos'][] = array(
					"image" => array (
							"id" => $artefactID,
							"view" => $viewID
							),
					"type" => "photo",
					"page" => array(
								"url" => $fullurl,
								"title" => $viewTitle
					),
					"owner" => array(
								"name" => $viewOwnerName,
								"profileurl" => $profileurl,
								"avatarurl" => $avatarurl
					)
				);
			}
		}
		
		$items = array(
                'count'	 => $data[sharedviews]->count,
                'data'   => $contents,
                'offset' => $offset,
                'limit'  => $limit,
        );


PluginBlocktypeGroupViewsImage::build_browse_list_html($items);

//$items = ArtefactTypeBrowse::get_browsable_items($filters, $offset, $limit);
//ArtefactTypeBrowse::build_browse_list_html($items);

json_reply(false, (object) array('message' => false, 'data' => $items));
