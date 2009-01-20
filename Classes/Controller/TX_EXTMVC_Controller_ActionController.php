<?php
declare(ENCODING = 'utf-8');

/*                                                                        *
 * This script belongs to the FLOW3 framework.                            *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License as published by the *
 * Free Software Foundation, either version 3 of the License, or (at your *
 * option) any later version.                                             *
 *                                                                        *
 * This script is distributed in the hope that it will be useful, but     *
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHAN-    *
 * TABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser       *
 * General Public License for more details.                               *
 *                                                                        *
 * You should have received a copy of the GNU Lesser General Public       *
 * License along with the script.                                         *
 * If not, see http://www.gnu.org/licenses/lgpl.html                      *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

/**
 * A multi action controller
 *
 * @version $Id:$
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
class ActionController extends TX_EXTMVC_Controller_RequestHandlingController {

	/**
	 * @var F3_FLOW3_Object_ManagerInterface
	 */
	protected $objectManager;

	/**
	 * @var boolean If initializeView() should be called on an action invocation.
	 */
	protected $initializeView = TRUE;

	/**
	 * @var TX_EXTMVC_View_AbstractView By default a view with the same name as the current action is provided. Contains NULL if none was found.
	 */
	protected $view = NULL;

	/**
	 * Injects the object manager
	 *
	 * @param F3_FLOW3_Object_ManagerInterface $objectManager
	 * @return void
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function injectObjectManager(F3_FLOW3_Object_ManagerInterface $objectManager) {
		$this->objectManager = $objectManager;
	}

	/**
	 * Handles a request. The result output is returned by altering the given response.
	 *
	 * @param TX_EXTMVC_Request $request The request object
	 * @param TX_EXTMVC_Response $response The response, modified by this handler
	 * @return void
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function processRequest(TX_EXTMVC_Request $request, TX_EXTMVC_Response $response) {
		parent::processRequest($request, $response);
		$this->callActionMethod();
	}

	/**
	 * Determines the name of the requested action and calls the action method accordingly.
	 * If no action was specified, the "default" action is assumed.
	 *
	 * @return void
	 * @author Robert Lemke <robert@typo3.org>
	 * @throws TX_EXTMVC_Exception_NoSuchAction if the action specified in the request object does not exist (and if there's no default action either).
	 */
	protected function callActionMethod() {
		$actionMethodName = $this->request->getControllerActionName() . 'Action';

		if (!method_exists($this, $actionMethodName)) throw new TX_EXTMVC_Exception_NoSuchAction('An action "' . $this->request->getControllerActionName() . '" does not exist in controller "' . get_class($this) . '".', 1186669086);
		$this->initializeAction();
		if ($this->initializeView) $this->initializeView();
		$actionResult = call_user_func_array(array($this, $actionMethodName), array());
		if (is_string($actionResult) && F3_PHP6_Functions::strlen($actionResult) > 0) {
			$this->response->appendContent($actionResult);
		}
	}

	/**
	 * Prepares a view for the current action and stores it in $this->view.
	 * By default, this method tries to locate a view with a name matching
	 * the current action.
	 *
	 * @return void
	 * @author Robert Lemke <robert@typo3.org>
	 */
	protected function initializeView() {
		$viewObjectName = $this->request->getViewObjectName();
		if ($viewObjectName === FALSE) {
			$viewObjectName = 'F3_FLOW3_MVC_View_EmptyView';
		}
		$this->view = $this->objectManager->getObject($viewObjectName);
		$this->view->setRequest($this->request);
	}

	/**
	 * Initializes the controller before invoking an action method.
	 *
	 * Override this method to solve tasks which all actions have in
	 * common.
	 *
	 * @return void
	 * @author Robert Lemke <robert@typo3.org>
	 */
	protected function initializeAction() {
	}

	/**
	 * The default action of this controller.
	 *
	 * This method should always be overridden by the concrete action
	 * controller implementation.
	 *
	 * @return void
	 * @author Robert Lemke <robert@typo3.org>
	 */
	protected function indexAction() {
		return 'No index action has been implemented yet for this controller.';
	}
}
?>