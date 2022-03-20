<?php

namespace App\OnlineFriends\Parser;

use App\OnlineFriends\Model\Friend;
use App\OnlineFriends\Token\Token;
use DOMElement;
use Goutte\Client;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\DomCrawler\Form;

class Parser
{
    private Client $goutte;

    public function __construct()
    {
        $this->goutte = new Client();
    }

    public function __invoke(string $username, string $password): iterable
    {
        $this->goutte->followRedirects(true);

        $crawler = $this->goutte->request('GET', 'https://id.secondlife.com/openid/login');

        $form = $this->getForm($crawler, 'form[id=loginform]');
        $form['username'] = $username;
        $form['password'] = $password;
        $form['return_to'] = 'https://secondlife.com/my/account/friends.php';
        $form['csrfmiddlewaretoken'] = $form->get('csrfmiddlewaretoken')->getValue();

        $crawler = $this->goutte->submit($form);

        $form = $this->getForm($crawler, 'form[id=openid_message]');
        $crawler = $this->goutte->submit($form);

        $nodes = $crawler->filter('div[id=main-content]')
            ->filter('.main-content-body')
            ->filter('table')->last()
            ->filter('li');

        /** @var DOMElement $node */
        foreach ($nodes as $node) {
            if (str_contains($node->nodeValue, '(')) {
                preg_match('/(?<displayName>[^()]+)\((?<username>[.\w\s]+)\)/', $node->nodeValue, $matches);

                $username = $matches['username'];
                $displayName = $matches['displayName'];
            } else {
                $username = $node->nodeValue;
                $displayName = $node->nodeValue;
            }

            yield new Friend($username, $displayName);
        }
    }

    private function getForm(Crawler $crawler, string $selector): Form
    {
        return $crawler->filter($selector)->form();
    }
}
