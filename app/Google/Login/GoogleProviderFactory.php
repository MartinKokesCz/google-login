<?php

namespace App\Google\Login;

use League\OAuth2\Client\Provider\Google;
use Nette\Application\LinkGenerator;

final class GoogleProviderFactory
{

	/** @var string */
	private $clientId;

	/** @var string */
	private $clientSecret;

	/** @var LinkGenerator */
	private $linkGenerator;

	public function __construct(
		string $clientId,
		string $clientSecret,
		LinkGenerator $linkGenerator
	)
	{
		$this->clientId = $clientId;
		$this->clientSecret = $clientSecret;
		$this->linkGenerator = $linkGenerator;
	}

	public function create(): Google
	{
		return new Google([
			'clientId' => $this->clientId,
			'clientSecret' => $this->clientSecret,
			'redirectUri' => $this->linkGenerator->link('Admin:Sign:google'),
		]);
	}

}