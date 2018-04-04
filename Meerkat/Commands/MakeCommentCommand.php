<?php

namespace Statamic\Addons\Meerkat\Commands;

use Carbon\Carbon;
use Statamic\API\YAML;
use Statamic\Extend\Command;

class MakeCommentCommand extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:comment';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Makes a comment from XML stuffs.';

    protected $data = <<<DATA
<wp:comment>
			<wp:comment_id>5009</wp:comment_id>
			<wp:comment_author><![CDATA[John]]></wp:comment_author>
			<wp:comment_author_email><![CDATA[john@stillat.com]]></wp:comment_author_email>
			<wp:comment_author_url>http://www.stillat.com</wp:comment_author_url>
			<wp:comment_author_IP><![CDATA[24.220.107.17]]></wp:comment_author_IP>
			<wp:comment_date><![CDATA[2015-02-20 12:32:57]]></wp:comment_date>
			<wp:comment_date_gmt><![CDATA[2015-02-20 18:32:57]]></wp:comment_date_gmt>
			<wp:comment_content><![CDATA[Hello Jayant! I'm glad the article was helpful, and thank you for taking the time to leave a comment!]]></wp:comment_content>
			<wp:comment_approved><![CDATA[1]]></wp:comment_approved>
			<wp:comment_type><![CDATA[]]></wp:comment_type>
			<wp:comment_parent>5005</wp:comment_parent>
			<wp:comment_user_id>1</wp:comment_user_id>
			<wp:commentmeta>
				<wp:meta_key><![CDATA[akismet_result]]></wp:meta_key>
				<wp:meta_value><![CDATA[false]]></wp:meta_value>
			</wp:commentmeta>
			<wp:commentmeta>
				<wp:meta_key><![CDATA[akismet_history]]></wp:meta_key>
				<wp:meta_value><![CDATA[a:4:{s:4:"time";d:1424457177.3997271060943603515625;s:7:"message";s:28:"Akismet cleared this comment";s:5:"event";s:9:"check-ham";s:4:"user";s:7:"stillat";}]]></wp:meta_value>
			</wp:commentmeta>
		</wp:comment>
DATA;


    private function getValue($value)
    {
        // Remove CDATA
        $value = str_replace("<![CDATA[","",$value);
        $value = str_replace("]]>","",$value);

        $value = strtr($value, [
            '<code>' => '`',
            '</code>' => '`',
            '<em>' => '*',
            '</em>' => '*',
            '<strong' => '**',
            '</strong>' => '**'
        ]);

        return strip_tags($value);
    }

    public function handle()
    {
        $wd = getcwd();
        $doc = new \SimpleXMLElement(strtr($this->data, [
            'wp:' => '',
        ]));

        $map = [
            'comment' => 'comment_content',
            'name' => 'comment_author',
            'email' => 'comment_author_email',
            'url' => 'comment_author_url',
            'user_ip' => 'comment_author_IP',
            'id' => 'comment_date'
        ];

        $comment = [];

        foreach ($map as $des => $origin) {
            $comment[$des] = $doc->{$origin};
            $comment[$des] = $comment[$des]->asXml();
            $comment[$des] = $this->getValue($comment[$des]);
        }

        $date = Carbon::parse($comment['id']);

        $comment['id'] = $date->getTimestamp();
        $comment['published'] = 'true';

        $content = YAML::dump($comment);


        if (!file_exists("$wd/{$comment['id']}")) {
            mkdir("$wd/{$comment['id']}", 0777);
        }

        file_put_contents("$wd/{$comment['id']}/comment.md", $content);
    }

}