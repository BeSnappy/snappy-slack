<?php namespace Snappy\Apps\Slack;


use Snappy\Apps\App as BaseApp;
use Snappy\Apps\IncomingMessageHandler;
use Snappy\Apps\TicketRepliedHandler;
use Snappy\Apps\TicketWaitingHandler;
use Snappy\Apps\WallPostHandler;

class App extends BaseApp implements WallPostHandler, IncomingMessageHandler, TicketRepliedHandler
{

    /**
     * The name of the application.
     *
     * @var string
     */
    public $name = 'Slack';

    /**
     * The application description.
     *
     * @var string
     */
    public $description = 'Send new incoming tickets notifications to Slack';

    /**
     * Any notes about this application
     *
     * @var string
     */
    public $notes = '<p>You can create a new <a href="https://my.slack.com/services/new/incoming-webhook" target="_blank">Incoming Webhook</a> on Slack via their custom integrations setup.</p>';

    /**
     * The application's icon filename.
     *
     * @var string
     */
    public $icon = 'slack.png';

    /**
     * The application service's website.
     *
     * @var string
     */
    public $website = 'https://slack.com/';

    /**
     * The application author name.
     *
     * @var string
     */
    public $author = 'Snappy';

    /**
     * The application author e-mail.
     *
     * @var string
     */
    public $email = 'hello@help.besnappy.com';

    /**
     * The settings required by the application.
     *
     * @var array
     */
    public $settings = array(
        array( 'name' => 'endpoint', 'type' => 'text', 'help' => 'Enter your Slack Incoming Webhook', 'validate' => 'required' ),
        array( 'name' => 'channel', 'type' => 'text', 'help' => 'Enter your Slack channel for your messages', 'placeholder' => '#support', 'validate' => 'required' ),

        // commented out for now, will be set internally by this class for now
        //array( 'name' => 'username', 'type' => 'text', 'help' => 'The username who will be sending the message to you (optional)', 'placeholder' => 'Snappy' ),
        //array( 'name' => 'icon', 'type' => 'text', 'help' => 'The icon (url or emoji) for the user who is sending the message to you (optional)' ),

        array( 'name' => 'new_ticket_notify', 'label' => 'New Tickets', 'type' => 'checkbox', 'help' => 'Notify on new inbound tickets?' ),
        array( 'name' => 'replied_ticket_notify', 'label' => 'Replied Tickets', 'type' => 'checkbox', 'help' => 'Notify on replied to tickets?' ),
        array( 'name' => 'wall_notify', 'label' => 'Wall', 'type' => 'checkbox', 'help' => 'Notify on new wall posts?' ),
    );

    /**
     * Wall post added.
     *
     * @param  array $wall
     *
     * @return void
     */
    public function handleWallPost( array $wall )
    {
        if( $this->config[ 'wall_notify' ] )
        {
            $this->getClient()->send(
                '*New Wall Post* by ' . $wall[ 'staff' ][ 'first_name' ] . ' ' . $wall[ 'staff' ][ 'last_name' ] . PHP_EOL .
                strip_tags( $wall[ 'content' ] ) . ' - https://app.besnappy.com/#wall'
            );
        }
    }

    /**
     * Check the incoming message for a tag.
     *
     * @param  array $message
     *
     * @return void
     */
    public function handleIncomingMessage( array $message )
    {
        if( $this->config[ 'new_ticket_notify' ] )
        {
            $url = 'https://app.besnappy.com/#ticket/' . $message[ 'ticket' ][ 'id' ];

            $this->getClient()->send(
                '*New Ticket* by ' . $message[ 'ticket' ][ 'opener' ][ 'value' ] . PHP_EOL .
                '*' . strip_tags( $message[ 'ticket' ][ 'default_subject' ] ) . '*' . PHP_EOL .
                strip_tags( $message[ 'ticket' ][ 'summary' ] ) . ' - ' . $url
            );
        }
    }

    /**
     * Handle a ticket with a status that is now "replied".
     *
     * @param  array $ticket
     *
     * @return void
     */
    public function handleTicketReplied( array $ticket )
    {
        if( $this->config[ 'replied_ticket_notify' ] )
        {
            $url = 'https://app.besnappy.com/#ticket/' . $ticket[ 'id' ];

            $this->getClient()->send(
                '*Replied to Ticket*' . PHP_EOL .
                '*' . strip_tags( $ticket[ 'default_subject' ] ) . '*' . PHP_EOL .
                strip_tags( $ticket[ 'summary' ] ) . ' - ' . $url
            );
        }
    }

    /**
     * Get the Hipchat client instance.
     *
     * @return \Maknz\Slack\Client
     */
    public function getClient()
    {
        return new \Maknz\Slack\Client(
            $this->config[ 'endpoint' ], array(
                'username'       => 'SnappyBot',
                'channel'        => $this->config[ 'channel' ],
                'icon'           => 'https://besnappy.com/images/bot.png',
                'allow_markdown' => true
            )
        );
    }
}

