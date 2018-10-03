<?php
/**
 * AJAX handler to look up DOI data.
 *
 * PHP version 7
 *
 * Copyright (C) Villanova University 2018.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License version 2,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
 *
 * @category VuFind
 * @package  AJAX
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     https://vufind.org/wiki/development Wiki
 */
namespace VuFind\AjaxHandler;

use VuFind\I18n\Translator\TranslatorAwareInterface;
use VuFindSearch\Backend\BrowZine\Connector;
use Zend\Mvc\Controller\Plugin\Params;

/**
 * AJAX handler to look up DOI data.
 *
 * @category VuFind
 * @package  AJAX
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     https://vufind.org/wiki/development Wiki
 */
class DoiLookup extends AbstractBase implements TranslatorAwareInterface
{
    use \VuFind\I18n\Translator\TranslatorAwareTrait;

    /**
     * BrowZine connector
     *
     * @var Connector
     */
    protected $connector;

    /**
     * Constructor
     *
     * @param Connector $connector Connector
     */
    public function __construct(Connector $connector)
    {
        $this->connector = $connector;
    }

    /**
     * Handle a request.
     *
     * @param Params $params Parameter helper from controller
     *
     * @return array [response data, HTTP status code]
     */
    public function handleRequest(Params $params)
    {
        $response = [];
        foreach ((array)$params->fromQuery('doi', []) as $doi) {
            $data = $this->connector->lookupDoi($doi)['data'] ?? null;
            if (!empty($data['browzineWebLink'])) {
                $response[$doi][] = [
                    'link' => $data['browzineWebLink'],
                    'label' => $this->translate('View Complete Issue'),
                    'data' => $data,
                ];
            }
            if (!empty($data['fullTextFile'])) {
                $response[$doi][] = [
                    'link' => $data['fullTextFile'],
                    'label' => $this->translate('PDF Full Text'),
                    'data' => $data,
                ];
            }
        }
        return $this->formatResponse($response);
    }
}