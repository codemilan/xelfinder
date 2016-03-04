<?php
class xelFinder extends elFinder {

	private $isAdmin = false;
	
	/**
	 * Constructor
	 *
	 * @param  array  elFinder and roots configurations
	 * @return void
	 * @author nao-pon
	 **/
	public function __construct($opts) {
		parent::__construct($opts);
		$this->isAdmin = $opts['isAdmin'];
		$this->commands['perm'] = array('target' => true, 'perm' => true, 'umask' => false, 'gids' => false, 'filter' => false, 'uid' => false);
	}

	
	
	/**
	* Set perm
	*
	* @param  array  $args  command arguments
	* @return array
	* @author nao-pon
	**/
	protected function perm($args) {

		$targets = $args['target'];
		if (!is_array($targets)) {
			$targets = array($targets);
		}

		if (($volume = $this->volume($targets[0])) != false) {
			if (method_exists($volume, 'savePerm')) {
				if ($volume->commandDisabled('perm')) {
					return array('error' => $this->error(self::ERROR_PERM_DENIED));
				}

				$uid = ($this->isAdmin && is_numeric($args['uid']))? intval($args['uid']) : null;
				// @todo uid 存在するか？妥当性検査
				
				if ($args['perm'] === 'getgroups') {
					$groups = $volume->getGroups($targets[0]);
					return $groups? $groups : array('error' => $this->error($volume->error()));
				} else {
					$files = array();
					$errors = array();
					foreach($targets as $target) {
						if (!isset($args['filter'])) $args['filter'] = '';
						$file = $volume->savePerm($target, $args['perm'], $args['umask'], $args['gids'], $args['filter'], $uid);
						if ($file) {
							$files[] = $file;
						} else {
							$errors = array_merge($errors, $volume->error());
						}
					}
					$ret = array();
					if ($files) {
						$ret['changed'] = $files;
					} else {
						$ret['error'] = $this->error($errors);
					}
					return $ret;
				}
			}
		}
		return array('error' => $this->error(self::ERROR_UNKNOWN_CMD));
	}
}