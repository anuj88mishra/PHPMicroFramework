<?php
class HtmlClass
{
    public $divClass;
    public $tblClass;
    public $searchFormClass;
    public $searchControlClass;
    public $searchInputClass;
    public $searchButtonControlClass;
    public $searchButtonClass;
    // Pagination Classes
    public $pageControlClass;
    public $pageListClass;
    public $pageLinkClass;
    public $pageEllipsesClass;
    public $pageCurrentClass;
    // Add/Update specific attributes
    public $notifyClass;
    public $notifyMessage;
    public $fieldGroupClass;
    public $fieldControlClass;
    public $fieldInputClass;
    public $buttonClass;
    // Cards
    public $cardClass;
    public $cardHeaderClass;
    public $cardHeaderTitleClass;
    public $cardContentClass;
    public $cardFooterClass;
    public $cardFooterItemClass;

    public function __construct() {
        $this->divClass = "tableDiv";
        $this->tblClass = "table is-fullwidth";
        $this->searchFormClass = "field has-addons";
        $this->searchControlClass = "control is-expanded";
        $this->searchInputClass = "search input is-primary";
        $this->searchButtonControlClass = "control";
        $this->searchButtonClass = "button is-info";
        $this->pageControlClass = "pagination";
        $this->pageListClass = "pagination-list";
        $this->pageLinkClass = "pagination-link";
        $this->pageEllipsesClass = "pagination-ellipses";
        $this->pageCurrentClass = "pagination-link is-current";
        $this->notifyClass = "notification";
        $this->notifyMessage = "";
        $this->fieldGroupClass = "field";
        $this->fieldControlClass = "control";
        $this->fieldInputClass = "input";
        $this->buttonClass = "button";
        $this->cardClass = "card";
        $this->cardHeaderClass = "card-header";
        $this->cardHeaderTitleClass = "card-header-title";
        $this->cardContentClass = "card-content";
        $this->cardFooterClass = "card-footer";
        $this->cardFooterItemClass = "card-footer-item";
    }
    public function __destruct() {
        $this->divClass = null;
        $this->tblClass = null;
        $this->searchFormClass = null;
        $this->searchControlClass = null;
        $this->searchInputClass = null;
        $this->searchButtonControlClass = null;
        $this->searchButtonClass = null;
        $this->pageControlClass = null;
        $this->pageListClass = null;
        $this->pageLinkClass = null;
        $this->pageEllipsesClass = null;
        $this->pageCurrentClass = null;
        $this->notifyClass = null;
        $this->notifyMessage = null;
        $this->fieldGroupClass = null;
        $this->fieldControlClass = null;
        $this->fieldInputClass = null;
        $this->buttonClass = null;
        $this->cardHeaderClass = null;
        $this->cardHeaderTitleClass = null;
        $this->cardContentClass = null;
        $this->cardFooterClass = null;
        $this->cardFooterItemClass = null;
    }
}
