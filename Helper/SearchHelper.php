<?php

namespace Paustian\PMCIModule\Helper;

use Zikula\SearchModule\AbstractSearchable;

class SearchHelper extends AbstractSearchable
{
    /**
     * get the UI options for search form
     *
     * @param boolean $active if the module should be checked as active
     * @param array|null $modVars module form vars as previously set
     * @return string
     */
    public function getOptions($active, $modVars = null)
    {
        return $this->getContainer()->get('templating')->renderResponse('PaustianPMCIModule:Search:options.html.twig', array('active' => $active))->getContent();
    }


    /**
     * Get the search results
     *
     * @param array $words array of words to search for
     * @param string $searchType AND|OR|EXACT
     * @param array|null $modVars module form vars passed though
     * @return array
     */
    function getResults(array $words, $searchType = 'AND', $modVars = null)
    {
        /*$qb = $this->entityManager->createQueryBuilder();
        $qb->select('a')
            ->from('Paustian\BookModule\Entity\BookArticlesEntity', 'a');
        $whereExp = $this->formatWhere($qb, $words, ['a.title', 'a.contents'], $searchType);
        $qb->andWhere($whereExp);
        
        
        $query = $qb->getQuery();
        $results = $query->getResult();
        $returnArray = array();
        $sessionId = session_id();
        
        foreach ($results as $article) {
            $url = new RouteUrl('paustianpmcimodule_user_displayarticle', ['article' => $article->getAid()]);
            //make sure we have permission for this object.
            if (!SecurityUtil::checkPermission('Book::', $article['bid'] . "::" . $article['cid'], ACCESS_COMMENT)) {
                continue;
            }
            $returnArray[] = array(
                    'title' => $article->getTitle(),
                    'text' => $this->shorten_text($article->getContents()),
                    'module' => $this->name,
                    'created' => '',
                    'sesid' => $sessionId,
                    'url' => $url
                );
        }*/
        $returnArray = [];
        return $returnArray;
    }
}

