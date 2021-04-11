<?php
class GC_Filter_HTMLPurifier_Inline extends GC_Filter_HTMLPurifier
{
    protected $_options = array(
        //array('HTML', 'Allowed', 'a, abbr, acronym, b, blockquote, caption, cite, code, dd, del, dfn, div, dl, dt, em, i, ins, kbd, li, ol, p, pre, s, strike, strong, sub, sup, table, tbody, td, tfoot, th, thead, tr, tt, u, ul, var'),
        array('Core.EscapeInvalidTags',True),
        array('HTML.Doctype', 'XHTML 1.0 Strict'),
        //array('Core','EscapeInvalidChildren',True),
        array('HTML.Parent','span'),//default value is div
        array('AutoFormat.RemoveEmpty',True),
        //array('AutoFormat','AutoParagraph',True),//no good will create <p></p> for every <p></p>
        //array('Output','TidyFormat',True),//tidy has some errors such as adding whitespace to <pre> elements which should preserve whitespace!
    );
}