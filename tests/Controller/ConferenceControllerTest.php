<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ConferenceControllerTest extends WebTestCase
{
    /**
     * @var KernelBrowser
     */
    protected KernelBrowser $client;

    /**
     * @return void
     */
    public function setUp (): void
    {
        $this->client = static::createClient();
    }

    public function testIndex ()
    {
        $this->client->request('GET', '/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h2', 'Give your feedback!');
    }

    public function testConferencePage ()
    {
        $crawler = $this->client->request('GET', '/');

        $this->assertCount(3, $crawler->filter('h4'));

        $this->client->clickLink('View');
//        $client->click($crawler->filter('h4 + p a')->link());

        $this->assertPageTitleContains('Toronto');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h2', 'Toronto 2020');
        $this->assertSelectorExists('div:contains("There are 10 comments")');
    }

    public function testCommentSubmission ()
    {
        $this->client->request('GET', '/conference/brasilia-2021');

        $this->client->submitForm('Submit', [
            'comment_form[author]' => 'Rafiou',
            'comment_form[text]' => 'Some feedback from an automated functional test',
            'comment_form[email]' => 'rafiou@domain.com',
            'comment_form[photo]' => dirname(__DIR__, 2).'/public/images/under-construction.gif',
        ]);

        $this->assertResponseRedirects();
        $this->client->followRedirect();
        $this->assertSelectorExists('div:contains("There are 1 comments")');
    }
}
