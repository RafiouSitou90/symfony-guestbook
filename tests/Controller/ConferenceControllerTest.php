<?php

namespace App\Tests\Controller;

use App\Repository\CommentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
//use Symfony\Component\Panther\Client;
//use Symfony\Component\Panther\PantherTestCase;

//class ConferenceControllerTest extends PantherTestCase
class ConferenceControllerTest extends WebTestCase
{
//    /**
//     * @var Client
//     */
//    protected Client $client;

    /**
     * @var KernelBrowser
     */
    protected KernelBrowser $client;

    /**
     * @var EntityManagerInterface
     */
    protected EntityManagerInterface $entityManager;

    /**
     * @return void
     */
    public function setUp (): void
    {
//        $this->client = static::createPantherClient(['external_base_uri' => 'http://127.0.0.1:8000']);

        $this->client = static::createClient();
        $this->entityManager = self::$container->get('doctrine')->getManager();

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
            'comment_form[email]' => $email = 'rafiou@domain.com',
            'comment_form[photo]' => dirname(__DIR__, 2).'/public/images/under-construction.gif',
        ]);

        $this->assertResponseRedirects();
        $comment = self::$container->get(CommentRepository::class)->findOneByEmail($email);
        $comment->setState('published');
        $this->entityManager->flush();

        $this->client->followRedirect();
        $this->assertSelectorExists('div:contains("There are 1 comments")');
    }

    public function testMailerAssertions ()
    {
        $this->client->request('GET', '/');

        $this->assertEmailCount(1);
        $event = $this->getMailerEvent(0);

        $this->assertEmailIsQueued($event);

        $email = $this->getMailerMessage(0);
        $this->assertEmailHeaderSame($email, 'To', 'no-reply@symfony-guestbook.com');
        $this->assertEmailTextBodyContains($email, 'Bar');
        $this->assertEmailAttachmentCount($email, 1);
    }
}
