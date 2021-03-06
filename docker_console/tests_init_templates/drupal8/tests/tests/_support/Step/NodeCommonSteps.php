<?php

namespace Step;

use Drupal\Pages\AccessDenied;
use Drupal\Pages\NodePage;
use Drupal\Pages\Page;
use Codeception\Module\DrupalTestUser;

/**
 * Class NodeCommonSteps
 * @package Step
 */
trait NodeCommonSteps {

  use UserCommonSteps;

  /**
   * Create node as test user.
   *
   * @param $username
   *   Test username.
   * @param $content_type
   *   Content type.
   * @param $fields_config
   *   Field values.
   *
   * @return int|null
   *   Node ID or NULL.
   */
  public function createNewNodeAsUser($username, $content_type, $fields_config) {
    /** @var \AcceptanceTester $I */
    $I = $this;

    // The same value in different variable to see functions from trait UserCommonSteps.
    /** @var UserCommonSteps $U */
    $U = $this;

    // login as user
    /** @var DrupalTestUser $user */
    $user = $I->getTestUserByName($username);
    $U->login($user->name, $user->pass);

    // create node
    $nid = $I->createNode($I, $content_type, $fields_config, NULL, TRUE);
    if (empty($nid)) {
      $nid = $this->grabNodeNid();
    }
    $I->appendNodeToStorage($nid);
    $I->seeVar($nid);

    // logout
    $U->logout();

    return $nid;
  }

  /**
   * Grab Node ID.
   *
   * @return null
   *   Node ID or NULL.
   */
  public function grabNodeNid() {
    /** @var \AcceptanceTester $I */
    $I = $this;

    // Grab the node id from the Edit tab once the node has been saved.
    $edit_url = $I->grabAttributeFrom('ul.tabs.primary > li:nth-child(2) > a', 'href');
    $matches = array();

    if (preg_match('~/node/(\d+)/edit~', $edit_url, $matches)) {
      return $matches[1];
    }
    return null;
  }

  /**
   * Delete node as test user.
   *
   * @param $username
   *   Test username.
   * @param $nid
   *   ID of node to delete.
   *
   * @return int
   *   ID of deleted node.
   */
  public function deleteNodeAsUser($username, $nid) {
    /** @var \AcceptanceTester $I */
    $I = $this;

    // The same value in different variable to see functions from trait UserCommonSteps.
    /** @var UserCommonSteps $U */
    $U = $this;

    // login as user
    /** @var DrupalTestUser $user */
    $user = $I->getTestUserByName($username);
    $U->login($user->name, $user->pass);

    $I->deleteNodeFromStorage($nid);

    // delete node
    $I->amOnPage(NodePage::route($nid, 'edit'));

    $I->click('#edit-delete');
    $I->see('Are you sure you want to delete');

    // logout
    $U->logout();

    return $nid;
  }

  /**
   * Node loads.
   *
   * @param $nid
   *   ID of node to check.
   */
  public function seeNodePage($nid) {
    /** @var \AcceptanceTester $I */
    $I = $this;
    $I->amOnPage(NodePage::route($nid));
    $I->seeElement(NodePage::$footerRegion);
    $I->dontSee('Page not found', Page::$pageTitle);
    $I->dontSee('Access denied', Page::$pageTitle);
    //TODO: add better check
  }

  /**
   * Node access denied.
   *
   * @param $nid
   *   ID of node to check.
   */
  public function cantSeeNodePage($nid) {
    if($nid) {
      /** @var \AcceptanceTester $I */
      $I = $this;
      $I->amOnPage(NodePage::route($nid));
      $I->see(AccessDenied::$accessDeniedMessage, Page::$pageTitle);
    }
  }
}
