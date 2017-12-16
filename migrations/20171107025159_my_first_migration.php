<?php
use \Api\Migration\Migration;

class MyFirstMigration extends Migration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     *
     * The following commands can be used in this method and Phinx will
     * automatically reverse them when rolling back:
     *
     *    createTable
     *    renameTable
     *    addColumn
     *    renameColumn
     *    addIndex
     *    addForeignKey
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function up()  {


        $this->down();

        $this->schema->create('currencies', function(Illuminate\Database\Schema\Blueprint $table){
            // Auto-increment id 
            $table->increments('id');
            $table->string('name');
            $table->decimal('rate',20,12);
            $table->string('description');
            $table->integer('decimals')->default(2);
            $table->timestamps();
        });
        $this->schema->create('users', function(Illuminate\Database\Schema\Blueprint $table){
            // Auto-increment id 
            $table->increments('id');
            $table->string('username');
			$table->string('name');
            $table->string('email')->unique();
            $table->string('password_hash');
            $table->string('firstname');
            $table->string('lastname');
            $table->string('fbid');
            $table->string('fbtoken');
            $table->string('phone');
            $table->string('mobile_code');
            $table->string('col');
            $table->string('api_key');
            $table->integer('status')->default(0);
            $table->string('image');
            $table->string('locality');
            $table->string('currency');
            $table->string('gender');
            $table->integer('location_id');
            $table->integer('test')->default(0);
			$table->integer('stars');
			$table->integer('sales')->default(0);
			$table->string('bio');
			$table->string('website');
			$table->string('location_description');
			$table->integer('no_products')->default(0);
			$table->integer('no_followers')->default(0);
			$table->integer('no_following')->default(0);
            $table->timestamps();
        });

        $this->schema->create('locations', function(Illuminate\Database\Schema\Blueprint $table){
            // Auto-increment id 
            $table->increments('id');
            $table->integer('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users');
            $table->string('name');
            $table->string('latitude');
            $table->string('longitude');
            $table->string('address');
            $table->string('locality');
			$table->string('admin');
			$table->string('country');
            $table->timestamps();
        });

        $this->schema->create('items', function(Illuminate\Database\Schema\Blueprint $table){
            // Auto-increment id 
            $table->increments('id');
            $table->integer('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users');
            $table->string('title');
            $table->integer('price');
            $table->string('currency');
            $table->longText('description');
            $table->integer('quantity');
            $table->integer('num_sales')->default(0);
            $table->integer('type')->default(0);
            $table->integer('negotiable')->default(0);
            $table->integer('status')->default(0);
            $table->integer('test')->default(0);
            $table->string('image');
            $table->integer('location_id')->references('id')->on('locations');
            $table->timestamps();
        });
        $this->schema->create('following', function(Illuminate\Database\Schema\Blueprint $table){
            // Auto-increment id 
            $table->increments('id');
            $table->integer('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users');
            $table->integer('following_id')->unsigned();
            $table->foreign('following_id')->references('id')->on('users');
            $table->timestamps();
        });

        $this->schema->create('following_searches', function(Illuminate\Database\Schema\Blueprint $table){
            // Auto-increment id 
            $table->increments('id');
            $table->integer('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users');
            $table->string('q');
            $table->timestamps();
        });
        $this->schema->create('wanted', function(Illuminate\Database\Schema\Blueprint $table){
            // Auto-increment id 
            $table->increments('id');
            $table->integer('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users');
            $table->string('q');
            $table->integer('price');
            $table->string('currency');
            $table->string('comment');
            $table->timestamps();
        });
        $this->schema->create('likes', function(Illuminate\Database\Schema\Blueprint $table){
            // Auto-increment id 
            $table->increments('id');
            $table->integer('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users');
            $table->integer('item_id')->unsigned();
            $table->foreign('item_id')->references('id')->on('items');
            $table->timestamps();
        });
        $this->schema->create('bookmarks', function(Illuminate\Database\Schema\Blueprint $table){
            // Auto-increment id 
            $table->increments('id');
            $table->integer('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users');
            $table->integer('item_id')->unsigned();
            $table->foreign('item_id')->references('id')->on('items');
            $table->timestamps();
        });
        $this->schema->create('comments', function(Illuminate\Database\Schema\Blueprint $table){
            // Auto-increment id 
            $table->increments('id');
            $table->integer('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users');
            $table->integer('item_id')->unsigned();
            $table->foreign('item_id')->references('id')->on('items');
			$table->string('comment');
            $table->timestamps();
        });
        $this->schema->create('shares', function(Illuminate\Database\Schema\Blueprint $table){
            // Auto-increment id 
            $table->increments('id');
            $table->integer('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users');
            $table->integer('item_id')->unsigned();
            $table->foreign('item_id')->references('id')->on('items');
            $table->timestamps();
        });

        $this->schema->create('reports', function(Illuminate\Database\Schema\Blueprint $table){
            // Auto-increment id 
            $table->increments('id');
            $table->integer('reporter_id')->unsigned();
         //   $table->foreign('reporter_id')->references('id')->on('users');
            $table->integer('user_id')->unsigned();
      //      $table->foreign('user_id')->references('id')->on('users');
            $table->integer('item_id')->unsigned();
            $table->foreign('item_id')->references('id')->on('items');
            $table->string('comment');
            $table->timestamps();
        });

        $this->schema->create('conversations', function(Illuminate\Database\Schema\Blueprint $table){
            // Auto-increment id 
            $table->increments('id');
            $table->integer('item_id')->unsigned();
            $table->foreign('item_id')->references('id')->on('items');
            $table->timestamps();
        });
        $this->schema->create('conversation_participants', function(Illuminate\Database\Schema\Blueprint $table){
            // Auto-increment id 
            $table->increments('id');
            $table->integer('conversation_id')->unsigned();
            $table->foreign('conversation_id')->references('id')->on('conversations');
            $table->integer('user_id')->unsigned();
			$table->integer('unread')->unsigned();
            $table->foreign('user_id')->references('id')->on('users');
            $table->timestamps();
        });


        $this->schema->create('messages', function(Illuminate\Database\Schema\Blueprint $table){
            // Auto-increment id 
            $table->increments('id');
            $table->integer('conversation_id')->unsigned();
            $table->foreign('conversation_id')->references('id')->on('conversations');
            $table->integer('item_id')->unsigned();
         //   $table->foreign('item_id')->references('id')->on('items');
            $table->integer('offer');
		    $table->integer('user_id')->unsigned();
			$table->foreign('user_id')->references('id')->on('users');
            $table->string('currency');
            $table->integer('result');
			$table->integer('type');
            $table->string('message');
			
            $table->timestamps();
        });
        $this->schema->create('notification_text', function(Illuminate\Database\Schema\Blueprint $table){
            // Auto-increment id 
            $table->increments('id');
            $table->string('prefix');
            $table->string('suffix');
             $table->string('deeplink');            
            $table->timestamps();
        });

        $this->schema->create('notifications', function(Illuminate\Database\Schema\Blueprint $table){
            // Auto-increment id 
            $table->increments('id');
            $table->integer('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users');
            $table->integer('initiator_id')->unsigned();
            $table->foreign('initiator_id')->references('id')->on('users');
            $table->integer('item_id')->unsigned();
            $table->foreign('item_id')->references('id')->on('items');
            $table->string('deeplink_id');
            $table->string('value');
            $table->integer('type')->unsigned();
			 $table->foreign('type')->references('id')->on('notification_text');
            $table->timestamps();
        });
        $this->schema->create('maxbids', function(Illuminate\Database\Schema\Blueprint $table){
            // Auto-increment id 
            $table->increments('id');
            $table->integer('item_id')->unsigned();
            $table->foreign('item_id')->references('id')->on('items');
            $table->integer('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users');
            $table->integer('bid');
            $table->string('currency');
            $table->longText('description');
            $table->integer('quantity');
            $table->integer('status')->default(0);
            $table->string('images');
            $table->integer('location_id')->references('id')->on('locations');
            $table->timestamps();
        });


        $this->schema->create('sales', function(Illuminate\Database\Schema\Blueprint $table){
            // Auto-increment id 
            $table->increments('id');
            $table->integer('item_id')->unsigned();
            $table->foreign('item_id')->references('id')->on('items');
            $table->integer('buyer_id')->unsigned();
            $table->foreign('buyer_id')->references('id')->on('users');
            $table->integer('seller_id')->unsigned();
            $table->foreign('seller_id')->references('id')->on('users');
            $table->integer('purchaseprice');
            $table->string('purchasecurrency');
            $table->integer('saleprice');
            $table->string('salecurrency');
            $table->longText('shippingnotes');
            $table->integer('quantity');
            $table->longText('fromlocation_id')->references('id')->on('locations');
            $table->longText('tolocation_id')->references('id')->on('locations');
			$table->string('comment');
            $table->integer('status')->default(0);
            $table->timestamps();
        });

        $this->schema->create('images', function(Illuminate\Database\Schema\Blueprint $table){
            // Auto-increment id 
            $table->increments('id');
            $table->integer('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users');
            $table->integer('item_id')->unsigned();			
      //      $table->foreign('item_id')->references('id')->on('items')->nullable();
            $table->string('description')->nullable();
		    $table->string('image')->nullable();
            $table->integer('order')->nullable();
            $table->integer('status')->default(0);
            $table->timestamps();
        });

        $this->schema->create('tokens', function(Illuminate\Database\Schema\Blueprint $table){
            // Auto-increment id 
            $table->increments('id');
            $table->integer('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users');
            $table->string('type');
            $table->string('token');
            $table->timestamps();
        });



        // $stmt = $this->conn->prepare("INSERT INTO sessions(device, regid, user_id, api_key) values(:device, :regid, :user_id, :api_key)");

        $this->schema->create('sessions', function(Illuminate\Database\Schema\Blueprint $table){
            // Auto-increment id 
            $table->increments('id');
            $table->integer('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users');
            $table->string('device');
            $table->string('regid');
            $table->string('api_key');
            $table->string('fbtoken')->nullable();
            $table->timestamps();
            $table->integer('test')->default(0);
        });
		
		  $this->schema->create('user_settings', function(Illuminate\Database\Schema\Blueprint $table){
            // Auto-increment id 
            $table->increments('user_settings_id');       
            $table->string('currency');    
        	$table->string('notify_message');  	
            $table->string('notify_offer');  
            $table->string('notify_comment');  
            $table->string('notify_review'); 
            $table->string('notify_tag');  	
            $table->integer('notify_followed'); 
            $table->integer('notify_friendjoins'); 		
            $table->string('save_originals'); 					
            $table->integer('id')->default(0);
			$table->timestamps();
        });
		  $this->schema->create('offers', function(Illuminate\Database\Schema\Blueprint $table){
            // Auto-increment id 
              $table->increments('offer_id');       
              $table->string('result')->nullable();    
              $table->string('currency');
			  $table->integer('message_id')->unsigned();
			  $table->foreign('message_id')->references('id')->on('messages');
			  $table->integer('price');
			  $table->integer('user_id')->unsigned();
			  $table->timestamps();
        });
		  $this->schema->create('reviews', function(Illuminate\Database\Schema\Blueprint $table){
            // Auto-increment id 
              $table->increments('id');       
              $table->integer('rated_user_id')->unsigned();    
			  $table->foreign('rated_user_id')->references('id')->on('users');
			  $table->integer('rater_user_id')->unsigned();    
			  $table->foreign('rater_user_id')->references('id')->on('users');
			  $table->integer('sale_id')->unsigned();    
			  $table->foreign('sale_id')->references('id')->on('sales');
              $table->integer('rating');
			  $table->string('comment');
			  $table->integer('type');
			  $table->timestamps();
        });
       $this->schema->create('cart', function(Illuminate\Database\Schema\Blueprint $table){
            // Auto-increment id 
              $table->increments('id');       
              $table->integer('user_id')->unsigned();
			  $table->foreign('user_id')->references('id')->on('users');
			  $table->integer('item_id');
			  $table->foreign('item_id')->references('id')->on('items');
			  $table->integer('variant_id');
			  $table->integer('quantity');
			  $table->timestamps();
        });
		 $this->schema->create('bids', function(Illuminate\Database\Schema\Blueprint $table){
            // Auto-increment id 
              $table->increments('id');       
              $table->integer('user_id')->unsigned();
			  $table->foreign('user_id')->references('id')->on('users');
			  $table->integer('item_id');
			  $table->foreign('item_id')->references('id')->on('items');
			  $table->integer('bid');
			
			  $table->timestamps();
        });
		 $this->schema->create('categories', function(Illuminate\Database\Schema\Blueprint $table){
            // Auto-increment id 
              $table->increments('id');       
              $table->string('hashtag');
			  $table->string('image');  		
			  $table->string('title');  	
			  $table->timestamps();
        });
		 $this->schema->create('max_bids', function(Illuminate\Database\Schema\Blueprint $table){
            // Auto-increment id 
               $table->increments('id');       
              $table->integer('user_id')->unsigned();
			  $table->foreign('user_id')->references('id')->on('users');
			  $table->integer('item_id');
			  $table->foreign('item_id')->references('id')->on('items');
			  $table->integer('bid');
			
        });
    }
    public function down()  {
    $this->schema->disableForeignKeyConstraints();
        $this->schema->dropIfExists('sessions');
        $this->schema->dropIfExists('maxbids');
        $this->schema->dropIfExists('sales');
        $this->schema->dropIfExists('images');
        $this->schema->dropIfExists('tokens');


        $this->schema->dropIfExists('notifications');
        $this->schema->dropIfExists('offers');
        $this->schema->dropIfExists('messages');
        $this->schema->dropIfExists('conversation_participants');
        $this->schema->dropIfExists('conversations');


        $this->schema->dropIfExists('reports');
        $this->schema->dropIfExists('shares');
        $this->schema->dropIfExists('comments');
        $this->schema->dropIfExists('bookmarks');
        $this->schema->dropIfExists('likes');


        $this->schema->dropIfExists('wanted');
        $this->schema->dropIfExists('following_searches');
        $this->schema->dropIfExists('following');

        $this->schema->dropIfExists('items');
        $this->schema->dropIfExists('currencies');
        $this->schema->dropIfExists('locations');
        $this->schema->dropIfExists('users');
        $this->schema->dropIfExists('notification_text');
        $this->schema->dropIfExists('user_settings');
        $this->schema->dropIfExists('reviews');
		$this->schema->dropIfExists('cart');
		$this->schema->dropIfExists('bids');
		$this->schema->dropIfExists('categories');
		$this->schema->dropIfExists('max_bids');
        $this->schema->enableForeignKeyConstraints();
    }
}
