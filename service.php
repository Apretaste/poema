<?php

use Goutte\Client;

class Poema extends Service
{
	/**
	 * Function executed when the service is called
	 *
	 * @param Request
	 * @return Response
	 * */
	public function _main(Request $request)
	{
		// create a new client
		$client = new Client();
		$guzzle = $client->getClient();
		$guzzle->setDefaultOption('verify', false);
		$client->setClient($guzzle);

		// create a crawler
		$crawler = $client->request('GET', "http://poemacadadia.blogspot.com/");

		// search for result
		$title = $crawler->filter('.post-title')->text();
		$poem = $crawler->filter('.post-body')->eq(0)->html();

		// remove tildes
		$poem = $this->utils->removeTildes($poem);

		// clears DIVs from text
		$dom = new DOMDocument;
		@$dom->loadHTML($poem);
		while (($r = $dom->getElementsByTagName("div")) && $r->length) {
			$r->item(0)->parentNode->removeChild($r->item(0));
		}

		// clean the author part
		$poem = $dom->saveHTML();
		$pos = strpos($poem, "</span></span><br><br>");
		$poem = substr($poem, $pos+22);

		// create a json object to send to the template
		$responseContent = array(
			"title" => $title,
			"poem" => $poem
		);

		// create the response
		$response = new Response();
		$response->setCache("day");
		$response->setResponseSubject("El poema del dia");
		$response->createFromTemplate("basic.tpl", $responseContent);
		return $response;
	}
}
