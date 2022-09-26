<?php

declare(strict_types=1);

namespace App\Module\Admin\Presenters;

use App\Forms;
use App\Model\GoogleUserFacade;
use League\OAuth2\Client\Provider\Google;
use Nette;
use Nette\Application\UI\Form;


final class SignPresenter extends Nette\Application\UI\Presenter
{

	/** @var Google */
	private $google;

    private $userRepository;

	/** @persistent */
	public string $backlink = '';

	private Forms\SignInFormFactory $signInFactory;

	private Forms\SignUpFormFactory $signUpFactory;


	public function __construct(Forms\SignInFormFactory $signInFactory, Forms\SignUpFormFactory $signUpFactory, Google $google, GoogleUserFacade $userRepository)
	{
		$this->signInFactory = $signInFactory;
		$this->signUpFactory = $signUpFactory;

		$this->google = $google;
        $this->userRepository = $userRepository;
	}


	/**
	 * Sign-in form factory.
	 */
	protected function createComponentSignInForm(): Form
	{
		return $this->signInFactory->create(function (): void {
			$this->restoreRequest($this->backlink);
			$this->redirect('Dashboard:');
		});
	}


	/**
	 * Sign-up form factory.
	 */
	protected function createComponentSignUpForm(): Form
	{
		return $this->signUpFactory->create(function (): void {
			$this->redirect('Dashboard:');
		});
	}


	public function actionOut(): void
	{
		$this->getUser()->logout();
		$this->flashMessage('... you have been signed out ...', 'success');
		$this->redirect(':Front:Homepage:');
	}

	public function actionGoogle(): void
	{
		$error = $this->getParameter('error');
		if ($error !== null) {
			$this->flashMessage('... google login error ...', 'error');
			$this->redirect(':Admin:Sign:in');
		}

		$state = $this->getParameter('state');
		$stateInSession = $this->getSession(Google::class)->state;
		if ($state === null || $stateInSession === null || ! \hash_equals($stateInSession, $state)) {
			$this->flashMessage('... invalid CSRF token ...', 'error');
			$this->redirect(':Admin:Sign:in');
		}

		// reset CSRF protection, it has done its job
		unset($this->getSession(Google::class)->state);

		$accessToken = $this->google->getAccessToken('authorization_code', [
			'code' => $this->getParameter('code'),
			'redirect_uri' => $this->link('//google'),
		]);

		try {
			/** @var GoogleUser $googleUser */
			$googleUser = $this->google->getResourceOwner($accessToken);
		} catch (\Throwable $e) {
			$this->flashMessage('... cannot retrieve user profile ...', 'error');
			$this->redirect(':Admin:Sign:in');
		}

		$googleId = $googleUser->getId();
		if ($user = $this->userRepository->findByGoogleId($googleId)) {
			// found existing user by googleId, login and redirect
			$this->user->login($user["username"], $googleUser->getId());

			if(in_array("admin", $this->user->roles)) {
				$this->redirect(':Admin:Dashboard:');
			}
			$this->redirect(':Front:Homepage:');
			
		}

		$googleEmail = $googleUser->getEmail();
		if ($user = $this->userRepository->findByEmail($googleEmail)) {
			// found existing user with the same email, error and force them to login using password
			$this->flashMessage('... somebody already signed up with given email ...', 'error');
			$this->redirect(':Admin:Sign:in');
		}

		// new user, register them, login and redirect
		$user = $this->userRepository->registerFromGoogle($googleUser);
		$this->user->login($user["username"], $user["password"]);
		$this->flashMessage('... welcome ...', 'success');
		$this->redirect(':Front:Homepage:');
	}

	public function handleGoogleLogin(): void
	{
		$authorizationUrl = $this->google->getAuthorizationUrl([
			'redirect_uri' => $this->link('//google'),
		]);

		$this->getSession(Google::class)->state = $this->google->getState();
		$this->redirectUrl($authorizationUrl);
	}

	
}
