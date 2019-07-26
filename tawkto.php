<?php
/**
 * Tawk.to
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to support@tawk.to so we can send you a copy immediately.
 *
 * @copyright   Copyright (c) 2014 Tawk.to
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

if (!defined('_TB_VERSION_')) {
    exit;
}

/**
 * Class TawkTo
 */
class TawkTo extends Module
{
    const TAWKTO_WIDGET_PAGE_ID = 'TAWKTO_WIDGET_PAGE_ID';
    const TAWKTO_WIDGET_WIDGET_ID = 'TAWKTO_WIDGET_WIDGET_ID';

    /**
     * TawkTo constructor.
     */
    public function __construct()
    {
        $this->name = 'tawkto';
        $this->tab = 'front_office_features';
        $this->version = '1.1.0';
        $this->author = 'thirty bees';
        $this->need_instance = 0;
        $this->tb_min_version = '1.0.0';
        $this->tb_versions_compliancy = '>= 1.0.0';

        parent::__construct();

        $this->displayName = $this->l('tawk.to');
        $this->description = $this->l('tawk.to live chat integration.');
    }

    /**
     * Install the module
     *
     * @return bool
     */
    public function install()
    {
        return parent::install() && $this->registerHook('footer');
    }

    /**
     * Uninstall the module
     *
     * @return bool
     */
    public function uninstall()
    {
        Configuration::deleteByName(static::TAWKTO_WIDGET_PAGE_ID);
        Configuration::deleteByName(static::TAWKTO_WIDGET_WIDGET_ID);

        return parent::uninstall();
    }

    /**
     * Module configuration page HTML
     *
     * @return string
     */
    public function getContent()
    {
        $this->context->smarty->assign([
            'iframe_url' => $this->getIframeUrl(),
            'base_url'   => $this->getBaseUrl(),
            'module_url' => $this->context->link->getAdminLink('AdminModules', true).'&'.http_build_query([
                'configure' => $this->name,
                'tab_module' => $this->tab,
                'module_name' => $this->name,
            ]),
        ]);

        return $this->display(__FILE__, 'views/templates/admin/view.tpl');
    }

    /**
     * Display footer
     *
     * @return string
     */
    public function hookDisplayFooter()
    {
        $pageId = Configuration::get(static::TAWKTO_WIDGET_PAGE_ID);
        $widgetId = Configuration::get(static::TAWKTO_WIDGET_WIDGET_ID);

        if (empty($pageId) || empty($widgetId)) {
            return '';
        }

        $this->context->smarty->assign(
            [
                'widget_id' => $widgetId,
                'page_id'   => $pageId,
            ]
        );

        return $this->display(__FILE__, 'widget.tpl');
    }

    /**
     * Ajax process - set widget
     */
    public function ajaxProcessSetWidget()
    {
        if (!isset($_POST['pageId']) || !isset($_POST['widgetId']) || !static::idsAreCorrect($_POST['pageId'], $_POST['widgetId'])) {
            die(json_encode(['success' => false]));
        }

        Configuration::updateValue(static::TAWKTO_WIDGET_PAGE_ID, $_POST['pageId']);
        Configuration::updateValue(static::TAWKTO_WIDGET_WIDGET_ID, $_POST['widgetId']);

        die(json_encode(['success' => true]));
    }

    /**
     * Ajax process - remove widget
     */
    public function ajaxProcessRemoveWidget()
    {
        Configuration::deleteByName(static::TAWKTO_WIDGET_PAGE_ID);
        Configuration::deleteByName(static::TAWKTO_WIDGET_WIDGET_ID);

        die(json_encode(['success' => true]));
    }

    /**
     * Get iframe URL
     *
     * @return string
     */
    private function getIframeUrl()
    {
        return $this->getBaseUrl()
            .'/generic/widgets'
            .'?currentPageId='.Configuration::get(static::TAWKTO_WIDGET_PAGE_ID)
            .'&currentWidgetId='.Configuration::get(static::TAWKTO_WIDGET_WIDGET_ID);
    }

    /**
     * Get base url
     *
     * @return string
     */
    private function getBaseUrl()
    {
        return 'https://plugins.tawk.to';
    }

    /**
     * Validate IDs
     *
     * @param string $pageId
     * @param string $widgetId
     *
     * @return bool
     */
    private static function idsAreCorrect($pageId, $widgetId)
    {
        return preg_match('/^[0-9A-Fa-f]{24}$/', $pageId) === 1 && preg_match('/^[a-z0-9]{1,50}$/i', $widgetId) === 1;
    }
}
