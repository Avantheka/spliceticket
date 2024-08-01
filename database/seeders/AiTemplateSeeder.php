<?php

namespace Database\Seeders;

use App\Models\Template;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AiTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $template = [
            [
                'template_name'=>'subject',
                'prompt'=>"generate example of  subject for bug in ecommerce base website support ticket",
                'module'=>'support',
                'field_json'=>'{"field":[{"label":"Ticket Description of Bug","placeholder":"e.g.Bug Summary","field_type":"textarea","field_name":"description"}]}',
                'is_tone'=>'0',
                "created_at" => date('Y-m-d H:i:s'),
                "updated_at" => date('Y-m-d H:i:s'),
            ],
            [
                'template_name'=>'description',
                'prompt'=>"generate support ticket description of  subject for ##subject## ",
                'module'=>'support',
                'field_json'=>'{"field":[{"label":"Ticket Subject","placeholder":"e.g.Error Message Displayed","field_type":"textarea","field_name":"subject"}]}',
                'is_tone'=>'1',
                "created_at" => date('Y-m-d H:i:s'),
                "updated_at" => date('Y-m-d H:i:s'),
            ],

            [
                'template_name'=>'reply_description',
                'prompt'=>"generate a short  replay note for support ticket that topic is '##title##'. user must be note that '##description##'.",
                'module'=>'reply',
                'field_json'=>'{"field":[{"label":"Ticket Title","placeholder":"Getting some issues while installation products.","field_type":"text_box","field_name":"title"},{"label":"Description","placeholder":"isuue is in his console account not in our product please follow google console api key creation step","field_type":"textarea","field_name":"description"}]}',
                'is_tone'=>'0',
                "created_at" => date('Y-m-d H:i:s'),
                "updated_at" => date('Y-m-d H:i:s'),
            ],

            [
                'template_name' => 'title',
                'prompt' => "IT company's web service support ticket system,please  suggested  some  number  of only topic name of Question  that asked by users repeatedlly in web service support relate to ##relate##.",
                'module' => 'faq',
                'field_json' => '{"field":[{"label":"FAQ Description","placeholder":"Installation","field_type":"text_box","field_name":"relate"}]}',
                'is_tone' => '0',
                "created_at" => date('Y-m-d H:i:s'),
                "updated_at" => date('Y-m-d H:i:s'),
            ],

            [
                'template_name' => 'description',
                'prompt' => "generate catchy detail user friendly description for this question topic : '##title##' please note that description should be usable for support ticket system",
                'module' => 'faq',
                'field_json' => '{"field":[{"label":"FAQ Title","placeholder":"Product Information","field_type":"text_box","field_name":"title"}]}',
                'is_tone' => '0',
                "created_at" => date('Y-m-d H:i:s'),
                "updated_at" => date('Y-m-d H:i:s'),

            ],

            [
                'template_name' => 'title',
                'prompt' => "list out category title of Knowledge in support ticket system for customer  the category title relate to the topic of '##title##'",
                'module' => 'knowledge_category',
                'field_json' => '{"field":[{"label":"Topic","placeholder":"Product Information","field_type":"text_box","field_name":"title"}]}',
                'is_tone' => '0',
                "created_at" => date('Y-m-d H:i:s'),
                "updated_at" => date('Y-m-d H:i:s'),
            ],

            [
                'template_name' => 'title',
                'prompt' => "list out  title of Knowledge base in support ticket system for customer the  title relate to the category of '##categoty##'",
                'module' => 'knowledge',
                'field_json' => '{"field":[{"label":"Knowledge Category Title","placeholder":"Installation","field_type":"text_box","field_name":"categoty"}]}',
                'is_tone' => '0',
                "created_at" => date('Y-m-d H:i:s'),
                "updated_at" => date('Y-m-d H:i:s'),
            ],

            [
                'template_name' => 'description',
                'prompt' => "generate catchy detail user friendly description for this knowledge base  title : '##title##' please note that description should be usable for support ticket system ",
                'module' => 'knowledge',
                'field_json' => '{"field":[{"label":"Title","placeholder":" How to Install Our Software","field_type":"text_box","field_name":"title"}]}',
                'is_tone' => '0',
                "created_at" => date('Y-m-d H:i:s'),
                "updated_at" => date('Y-m-d H:i:s'),
            ],


            [
                'template_name' => 'name',
                'prompt' => "list out category  of support  in website support ticket like(##retale##).",
                'module' => 'category',
                'field_json' => '{"field":[{"label":" Description","placeholder":"bug,Installation","field_type":"text_box","field_name":"relate"}]}',
                'is_tone' => '0',
                "created_at" => date('Y-m-d H:i:s'),
                "updated_at" => date('Y-m-d H:i:s'),
            ],

            [
                'template_name' => 'note',
                'prompt' => "generate a  note for support ticket that topic is '##title##'. in that note include this points '##description##'.",
                'module' => 'note',
                'field_json' => '{"field":[{"label":"Ticket Title","placeholder":"Getting some issues while installation products.","field_type":"text_box","field_name":"title"},{"label":"Description","placeholder":"isuue is in his console account not in our product please follow google console api key creation step","field_type":"textarea","field_name":"description"}]}',
                'is_tone' => '0',
                "created_at" => date('Y-m-d H:i:s'),
                "updated_at" => date('Y-m-d H:i:s'),
            ],


            [
                'template_name'=>'name',
                'prompt'=>"please suggest subscription plan  name  for this  :  ##description##  for my business",
                'module'=>'plan',
                'field_json'=>'{"field":[{"label":"What is your plan about?","placeholder":"e.g. Describe your plan details ","field_type":"textarea","field_name":"description"}]}',
                'is_tone'=>'0',
                "created_at" => date('Y-m-d H:i:s'),
                "updated_at" => date('Y-m-d H:i:s'),
            ],
            [
                'template_name'=>'description',
                'prompt'=>"please suggest subscription plan  description  for this  :  ##title##:  for my business",
                'module'=>'plan',
                'field_json'=>'{"field":[{"label":"What is your plan title?","placeholder":"e.g. Pro Resller,Exclusive Access","field_type":"text_box","field_name":"title"}]}',
                'is_tone'=>'1',
                "created_at" => date('Y-m-d H:i:s'),
                "updated_at" => date('Y-m-d H:i:s'),
            ],
            [
                'template_name'=>'name',
                'prompt'=>"give 10 catchy only name of Offer or discount Coupon for : ##keywords##",
                'module'=>'coupon',
                'field_json'=>'{"field":[{"label":"Seed words","placeholder":"e.g.coupon will provide you with a discount on your selected plan","field_type":"text_box","field_name":"keywords"}]}',
                'is_tone'=>'0',
                "created_at" => date('Y-m-d H:i:s'),
                "updated_at" => date('Y-m-d H:i:s'),
            ],
            [
                'template_name'=>'meta_keywords',
                'prompt'=>"Write SEO meta title for:\n\n ##description## \n\nWebsite name is:\n ##title## \n\nSeed words:\n ##keywords## \n\n",
                'module'=>'seo',
                'field_json'=>'{"field":[{"label":"Website Name","placeholder":"e.g. Amazon, Google","field_type":"text_box","field_name":"title"},{"label":"Website Description","placeholder":"e.g. Describe what your website or business do","field_type":"textarea","field_name":"description"},{"label":"Keywords","placeholder":"e.g.  cloud services, databases","field_type":"text_box","field_name":"keywords"}]}',
                'is_tone'=>'0',
                "created_at" => date('Y-m-d H:i:s'),
                "updated_at" => date('Y-m-d H:i:s'),
            ],
            [
                'template_name'=>'meta_description',
                'prompt'=>"Write SEO meta description for:\n\n ##description## \n\nWebsite name is:\n ##title## \n\nSeed words:\n ##keywords## \n\n",
                'module'=>'seo',
                'field_json'=>'{"field":[{"label":"Website Name","placeholder":"e.g. Amazon, Google","field_type":"text_box","field_name":"title"},{"label":"Website Description","placeholder":"e.g. Describe what your website or business do","field_type":"textarea","field_name":"description"},{"label":"Keywords","placeholder":"e.g.  cloud services, databases","field_type":"text_box","field_name":"keywords"}]}',
                'is_tone'=>'1',
                "created_at" => date('Y-m-d H:i:s'),
                "updated_at" => date('Y-m-d H:i:s'),
            ],[
                'template_name'=>'cookie_title',
                'prompt'=>"please suggest me cookie title for this ##description## website which i can use in my website cookie",
                'module'=>'cookie',
                'field_json'=>'{"field":[{"label":"Website name or info","placeholder":"e.g. example website ","field_type":"textarea","field_name":"title"}]}',
                'is_tone'=>'0',
                "created_at" => date('Y-m-d H:i:s'),
                "updated_at" => date('Y-m-d H:i:s'),
            ],[
                'template_name'=>'cookie_description',
                'prompt'=>"please suggest me  Cookie description for this cookie title ##title##  which i can use in my website cookie",
                'module'=>'cookie',
                'field_json'=>'{"field":[{"label":"Cookie Title ","placeholder":"e.g. example website ","field_type":"text_box","field_name":"title"}]}',
                'is_tone'=>'1',
                "created_at" => date('Y-m-d H:i:s'),
                "updated_at" => date('Y-m-d H:i:s'),
            ],
            [
                'template_name'=>'strictly_cookie_title',
                'prompt'=>"please suggest me only Strictly Cookie Title for this ##description## website which i can use in my website cookie",
                'module'=>'cookie',
                'field_json'=>'{"field":[{"label":"Website name or info","placeholder":"e.g. example website ","field_type":"textarea","field_name":"title"}]}',
                'is_tone'=>'0',
                "created_at" => date('Y-m-d H:i:s'),
                "updated_at" => date('Y-m-d H:i:s'),
            ],
            [
                'template_name'=>'strictly_cookie_description',
                'prompt'=>"please suggest me Strictly Cookie description for this Strictly cookie title ##title##  which i can use in my website cookie",
                'module'=>'cookie',
                'field_json'=>'{"field":[{"label":"Strictly Cookie Title ","placeholder":"e.g. example website ","field_type":"text_box","field_name":"title"}]}',
                'is_tone'=>'1',
                "created_at" => date('Y-m-d H:i:s'),
                "updated_at" => date('Y-m-d H:i:s'),
            ],
            [
                'template_name'=>'more_information_description',
                'prompt'=>"I need assistance in crafting compelling content for my ##web_name## website's 'Contact Us' page of my website. The page should provide relevant information to users, encourage them to reach out for inquiries, support, and feedback, and reflect the unique value proposition of my business.",
                'module'=>'cookie',
                'field_json'=>'{"field":[{"label":"Websit Name","placeholder":"e.g. example website ","field_type":"text_box","field_name":"web_name"}]}',
                'is_tone'=>'1',
                "created_at" => date('Y-m-d H:i:s'),
                "updated_at" => date('Y-m-d H:i:s'),
            ],
            [
                'template_name'=>'content',
                'prompt'=>"generate email template for ##type##",
                'module'=>'email template',
                'field_json'=>'{"field":[{"label":"Email Type","placeholder":"e.g. new user,new client","field_type":"text_box","field_name":"type"}]}',
                'is_tone'=>'1',
                "created_at" => date('Y-m-d H:i:s'),
                "updated_at" => date('Y-m-d H:i:s'),
            ],

            [
                'template_name'=>'content',
                'prompt'=>"Generate a meeting notification message for an ##topic## meeting. Include the date, time, location, and a brief agenda with three key discussion points.",
                'module'=>'notification template',
                'field_json'=>'{"field":[{"label":"Notification Message","placeholder":"e.g.brief explanation of the purpose or background of the notification","field_type":"textarea","field_name":"topic"}]}',
                'is_tone'=>'0',
                "created_at" => date('Y-m-d H:i:s'),
                "updated_at" => date('Y-m-d H:i:s'),
            ],
        ];
        Template::insert($template);
    }
}
