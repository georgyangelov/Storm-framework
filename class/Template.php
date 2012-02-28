<?php
/*
* Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:
*
* The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.
*
* THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
*/
class Template implements IStormResult
{
	public $TemplatePath, $UseOB, $OutputCompiled = false, $Output;
	public $RelativePath, $AbsolutePath;
	
	private $ReplaceSet = false, $Replace, $OBLevel;
	public $RegisterVariables = true, $OutputBuffer, $Vars = array();
	
	public function __construct($TemplatePath, $UseOutputBuffering = false, $RegisterVariables = true)
	{
		$this->OBLevel = ob_get_level();
		
		if ( $UseOutputBuffering )
			$this->StartOutputBuffer();
			
		$this->LoadTemplate($TemplatePath);
		$this->RegisterVariables = $RegisterVariables;
	}
	
	public static function Load($path, $vars = false, $show = true)
	{
		$t = new Template($path);
		
		if ( $vars !== false )
		$t->AddVars($vars);
		
		$out = $t->GetOutput();
		
		if ( $show )
			echo $out;

		return $out;
	}
	
	public function ProcessResult()
	{
		$this->ShowOutput();
	}
	
	public function StartOutputBuffer()
	{
		$this->UseOB = true;
		ob_start();
	}
	
	public function LoadTemplate($Template)
	{
		if ( !is_file($Template) )
			throw new CantLoadTemplateException($Template);
		
		$this->TemplatePath = $Template;
		
		$this->AbsolutePath = dirname($Template);
		$this->RelativePath = Storm::ToRelative($this->AbsolutePath);
	}
	
	public function StopOutputBuffer($GetAllLevels = false, $clear = false)
	{
		$this->OutputBuffer = '';
		
		if ( $GetAllLevels )
			while ( ob_get_level() )
				$this->OutputBuffer .= ob_get_clean();
		else
			while ( ob_get_level() > $this->OBLevel )
				$this->OutputBuffer .= ob_get_clean();
			
		$this->UseOB = false;
		
		if ( $clear )
		{
			$ob = $this->OutputBuffer;
			$this->OutputBuffer = '';
			
			return $ob;
		}
		
		return $this->OutputBuffer;
	}
	
	public function ClearOutputBuffer()
	{
		if ( $this->UseOB )
		{
			ob_end_clean();
			ob_start();
		}
	}
	
	public function AddVar($name, $val)
	{
		$this->Vars[$name] = $val;
	}
	
	public function ConcatVar($name, $val)
	{
		if ( !isset($this->Vars[$name]) )
			$this->AddVar($name, $val);
		else
			$this->Vars[$name] .= $val;
	}
	
	public function AddRef($name, &$ref)
	{
		$this->Vars[$name] = &$ref;
	}
	
	public function AddVars($array, $shouldOverride = true)
	{		
		if ( $shouldOverride )
			$this->Vars = array_merge($this->Vars, $array);
		else
			$this->Vars = array_merge($array, $this->Vars);
	}
	
	public function Redirect($Url, $Time = 5)
	{
		header("Refresh: ".$Time."; url=". $Url);
	}
	
	public function RegexReplace($search, $replace)
	{
		$this->ReplaceSet = true;
		
		$this->Replace[] = array('search' => $search, 'replace' => $replace);
	}
	
	private function CompileOutput()
	{
		if ( $this->OutputCompiled )
			return false;
		
		extract($this->Vars, EXTR_REFS);
		
		if ( $this->UseOB )
			$this->StopOutputBuffer();
			
		ob_start();
		require $this->TemplatePath;
		$this->Output = ob_get_clean();
		
		if ( $this->ReplaceSet )
			foreach ( $this->Replace as $r )
				$this->Output = preg_replace($r['search'], $r['replace'], $this->Output);
		
		$this->OutputCompiled = true;
		
		return true;
	}
	
	public function GetOutput()
	{
		$this->CompileOutput();
		
		return $this->Output;
	}
	
	public function ShowOutput()
	{
		echo $this->GetOutput();
	}
	
	public function __toString() 
	{
        return $this->GetOutput();
    }
}

class CantLoadTemplateException extends Exception
{
	public $path;
	
	public function __construct($path)
	{
		parent::__construct('Could not load template');
		
		$this->path = $path;
	}
}
?>