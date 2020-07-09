<?php
/**
 *
 * @package quickinstall
 * @copyright (c) 2007 phpBB Limited
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFunction;

/**
 * A slim template class to enable twig variables in templates
 * as well as phpBB template functions in PHP for assigning
 * template vars.
 */
class twig
{
	/**
	 * @var \Twig\Environment
	 */
	protected $twig;

	/**
	 * @var object User class
	 */
	protected $user;

	/**
	 * @var array
	 */
	protected $variables = [];

	/**
	 * Constructor
	 *
	 * @param object $user
	 * @param string $cachepath
	 * @param string $qi_root_path
	 */
	public function __construct($user, $cachepath, $qi_root_path)
	{
		$loader = new FilesystemLoader($qi_root_path . 'style/');
		$this->twig = new Environment($loader, [
			'cache' => $cachepath,
			'autoescape' => false,
			'auto_reload' => defined('QI_DEBUG'),
		]);

		$this->twig->addFunction(
			new TwigFunction('lang', [$this, 'lang'])
		);

		$this->user = $user;
	}

	/**
	 * Set the path to the cache
	 *
	 * @param string $cachepath
	 */
	public function set_cachepath($cachepath)
	{
		if ($this->twig->getCache() !== $cachepath)
		{
			$this->twig->setCache($cachepath);
		}
	}

	/**
	 * Render the template using Twig's render functionality
	 *
	 * @param string $templateFile Template file to load
	 *
	 * @throws \Twig\Error\LoaderError
	 * @throws \Twig\Error\RuntimeError
	 * @throws \Twig\Error\SyntaxError
	 */
	public function display($templateFile)
	{
		echo $this->twig->render("$templateFile.html", $this->variables);
	}

	/**
	 * Assign a single scalar value to a single key.
	 *
	 * Value can be a string, an integer or a boolean.
	 *
	 * @param string $varname Variable name
	 * @param string $varval Value to assign to variable
	 */
	public function assign_var($varname, $varval)
	{
		$this->variables[$varname] = $varval;
	}

	/**
	 * Assign key variable pairs from an array
	 *
	 * @param array $vararray A hash of variable name => value pairs
	 */

	public function assign_vars(array $vararray)
	{
		foreach ($vararray as $key => $value)
		{
			$this->assign_var($key, $value);
		}
	}

	/**
	 * Append text to the string value stored in a key.
	 *
	 * Text is appended using the string concatenation operator (.).
	 *
	 * @param string $varname Variable name
	 * @param string $varval Value to append to variable
	 */
	public function append_var($varname, $varval)
	{
		$this->variables[$varname] = (isset($this->variables[$varname]) ? $this->variables[$varname] : '') . $varval;
	}

	/**
	 * Assign key variable pairs from an array to a specified block
	 *
	 * @param string $blockname Name of block to assign $vararray to
	 * @param array $vararray A hash of variable name => value pairs
	 */
	public function assign_block_vars($blockname, array $vararray)
	{
		// For nested block, $blockcount > 0, for top-level block, $blockcount == 0
		$blocks = explode('.', $blockname);
		$blockcount = count($blocks) - 1;

		$block = &$this->variables;
		for ($i = 0; $i < $blockcount; $i++)
		{
			$pos = strpos($blocks[$i], '[');
			$name = ($pos !== false) ? substr($blocks[$i], 0, $pos) : $blocks[$i];
			$block = &$block[$name];
			$block_count = empty($block) ? 0 : count($block) - 1;
			$index = (!$pos || strpos($blocks[$i], '[]') === $pos) ? $block_count : (min((int) substr($blocks[$i], $pos + 1, -1), $block_count));
			$block = &$block[$index];
		}

		// $block = &$block[$blocks[$i]]; // Do not traverse the last block as it might be empty
		$name = $blocks[$i];

		// Now we add the block that we're actually assigning to.
		// We're adding a new iteration to this block with the given
		// variable assignments.
		$block[$name][] = $vararray;
	}

	/**
	 * Assign key variable pairs from an array to a whole specified block loop
	 *
	 * @param string $blockname Name of block to assign $block_vars_array to
	 * @param array $block_vars_array An array of hashes of variable name => value pairs
	 */
	public function assign_block_vars_array($blockname, array $block_vars_array)
	{
		foreach ($block_vars_array as $vararray)
		{
			$this->assign_block_vars($blockname, $vararray);
		}
	}

	/**
	 * Custom lang function translates a lang key from the template
	 *
	 * @return mixed
	 */
	public function lang()
	{
		$var = func_get_args();

		return isset($this->user->lang[$var[0]]) ? $this->user->lang[$var[0]] : $var[0];
	}
}
