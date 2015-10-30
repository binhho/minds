<?php
/**
 * Minds Market
 *
 * This is an OOP plugin and is an example of the new structure Minds plugins should follow.
 *
 * @package Minds.Core
 * @subpackage Plugins
 * @author Mark Harding (mark@minds.com)
 */

namespace minds\plugin\market;

use Minds\Core;
use Minds\Components;

class start extends Components\Plugin{

	public function __construct(){
		parent::__construct('market');

		$this->init();
	}

	public function init(){

		\elgg_register_plugin_hook_handler('entities_class_loader', 'all', function($hook, $type, $return, $row){
			if($row->subtype == 'market')
				return new entities\item($row);
			if($row->subtype == 'market_order')
				return new entities\order($row);
		});

		$routes = core\Router::registerRoutes($this->registerRoutes());

		\elgg_extend_view('css/elgg', 'market/css', 800);

		\elgg_register_plugin_hook_handler('register', 'menu:entity',array($this, 'menuOverride'), 900);
		\elgg_register_plugin_hook_handler('acl', 'all', array($this, 'acl'));
	}

	/**
	 * Handler the pages
	 *
	 * @param array $pages - the page slugs
	 * @return bool
	 */
	public function registerRoutes(){
		$path = "minds\\plugin\\market";
		return array(
			'/market' => "$path\\pages\\lists",
			'/market/item' => "$path\\pages\\view",
			'/market/add' => "$path\\pages\\edit",
			'/market/item/edit' => "$path\\pages\\edit",
			'/market/image' => "$path\\pages\\image",
			'/market/basket' => "$path\\pages\\basket",
			'/market/checkout' => "$path\\pages\\checkout",
			'/market/orders' => "$path\\pages\\orders",
			'/market/seller' => "$path\\pages\\seller"
		);
	}

	/**
	 * Categories for market items
	 *
	 * Categories are seperated by a ":" colon.
	 * @return array
	 */
	static public function getCategories(){

		if($categories = \elgg_get_plugin_setting('categories', 'market')){

			$categories = explode(',',$categories);
			return $categories;

		}

		return array(
			'uncategorised',
			'food',
			'food:chocolate',
			'technology',
			'fashion',
			'toys',
			'sports',
			'movies',
			'books',
			'services'
		);
	}

	public function menuOverride($hook, $type, $return, $params){
		if(isset($params['entity']) &&  $params['entity']->subtype == 'market'){

		$entity = $params['entity'];
		foreach($return as $k => $item){
			if(in_array($item->getName(), array('access', 'thumbs:up', 'thumbs:down', 'delete')))
				unset($return[$k]);
		}

		$options = array(
						'name' => 'edit',
						'href' => "market/item/edit/$entity->guid",
						'text' => 'Edit',
						'title' => elgg_echo('edit'),
						'priority' => 1,
					);
		$return[] = \ElggMenuItem::factory($options);

		$options = array(
						'name' => 'delete',
						'href' => "market/item/delete/$entity->guid",
						'text' => 'Delete',
						'title' => elgg_echo('delete'),
						'class'=>'ajax-non-action'
					);
		$return[] = \ElggMenuItem::factory($options);


		return $return;
		}
	}

	/**
	 * ACL extension to allow for seller access to placed orders
	 */
	 public function acl($hook, $type, $return, $params){
	 	$entity = $params['entity'];
		$user = $params['user'];
		if($entity->item['owner_guid'] == $user->guid)
			return true;

	//	return false;
	 }

}