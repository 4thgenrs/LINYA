<!-- ===== UNIVERSAL FLOATING KEYBOARD ===== -->
<div class="floating-keyboard-container" id="floatingKeyboardContainer">
    <div class="floating-keyboard" id="mainKeyboard">
        <!-- Row 1: Numbers -->
        <div class="keyboard-row">
            <button class="keyboard-key" data-key="1">1</button>
            <button class="keyboard-key" data-key="2">2</button>
            <button class="keyboard-key" data-key="3">3</button>
            <button class="keyboard-key" data-key="4">4</button>
            <button class="keyboard-key" data-key="5">5</button>
            <button class="keyboard-key" data-key="6">6</button>
            <button class="keyboard-key" data-key="7">7</button>
            <button class="keyboard-key" data-key="8">8</button>
            <button class="keyboard-key" data-key="9">9</button>
            <button class="keyboard-key" data-key="0">0</button>
        </div>
        
        <!-- Row 2: QWERTYUIOP -->
        <div class="keyboard-row">
            <button class="keyboard-key" data-key="q">q</button>
            <button class="keyboard-key" data-key="w">w</button>
            <button class="keyboard-key" data-key="e">e</button>
            <button class="keyboard-key" data-key="r">r</button>
            <button class="keyboard-key" data-key="t">t</button>
            <button class="keyboard-key" data-key="y">y</button>
            <button class="keyboard-key" data-key="u">u</button>
            <button class="keyboard-key" data-key="i">i</button>
            <button class="keyboard-key" data-key="o">o</button>
            <button class="keyboard-key" data-key="p">p</button>
        </div>
        
        <!-- Row 3: ASDFGHJKL -->
        <div class="keyboard-row">
            <button class="keyboard-key" data-key="a">a</button>
            <button class="keyboard-key" data-key="s">s</button>
            <button class="keyboard-key" data-key="d">d</button>
            <button class="keyboard-key" data-key="f">f</button>
            <button class="keyboard-key" data-key="g">g</button>
            <button class="keyboard-key" data-key="h">h</button>
            <button class="keyboard-key" data-key="j">j</button>
            <button class="keyboard-key" data-key="k">k</button>
            <button class="keyboard-key" data-key="l">l</button>
        </div>
        
        <!-- Row 4: ZXCVBNM + Shift + Backspace -->
        <div class="keyboard-row">
            <button class="keyboard-key wide" id="shiftBtn" data-action="shift">⬆️</button>
            <button class="keyboard-key" data-key="z">z</button>
            <button class="keyboard-key" data-key="x">x</button>
            <button class="keyboard-key" data-key="c">c</button>
            <button class="keyboard-key" data-key="v">v</button>
            <button class="keyboard-key" data-key="b">b</button>
            <button class="keyboard-key" data-key="n">n</button>
            <button class="keyboard-key" data-key="m">m</button>
            <button class="keyboard-key wide" data-action="backspace">⌫</button>
        </div>
        
        <!-- Row 5: Symbols, Space, Clear, Done -->
        <div class="keyboard-row">
            <button class="keyboard-key wide" id="symBtn" data-action="symbols">.?123</button>
            <button class="keyboard-key" data-key="@">@</button>
            <button class="keyboard-key" data-key=".">.</button>
            <button class="keyboard-key" data-key=",">,</button>
            <button class="keyboard-key extra-wide space" data-action="space">␣ Space</button>
            <button class="keyboard-key special extra-wide" data-action="clear">Clear</button>
            <button class="keyboard-key special extra-wide" data-action="done">✓ Done</button>
        </div>
    </div>
    
    <!-- Symbol Keyboard (Hidden by default) -->
    <div class="floating-keyboard" id="symbolKeyboard" style="display: none;">
        <!-- Row 1: Symbols !@#$%^&*() -->
        <div class="keyboard-row">
            <button class="keyboard-key" data-key="!">!</button>
            <button class="keyboard-key" data-key="@">@</button>
            <button class="keyboard-key" data-key="#">#</button>
            <button class="keyboard-key" data-key="$">$</button>
            <button class="keyboard-key" data-key="%">%</button>
            <button class="keyboard-key" data-key="^">^</button>
            <button class="keyboard-key" data-key="&">&amp;</button>
            <button class="keyboard-key" data-key="*">*</button>
            <button class="keyboard-key" data-key="(">(</button>
            <button class="keyboard-key" data-key=")">)</button>
        </div>
        
        <!-- Row 2: -_=+[]{}|\ -->
        <div class="keyboard-row">
            <button class="keyboard-key" data-key="-">-</button>
            <button class="keyboard-key" data-key="_">_</button>
            <button class="keyboard-key" data-key="=">=</button>
            <button class="keyboard-key" data-key="+">+</button>
            <button class="keyboard-key" data-key="[">[</button>
            <button class="keyboard-key" data-key="]">]</button>
            <button class="keyboard-key" data-key="{">{</button>
            <button class="keyboard-key" data-key="}">}</button>
            <button class="keyboard-key" data-key="\">\</button>
            <button class="keyboard-key" data-key="|">|</button>
        </div>
        
        <!-- Row 3: ;:'",<.>/? -->
        <div class="keyboard-row">
            <button class="keyboard-key" data-key=";">;</button>
            <button class="keyboard-key" data-key=":">:</button>
            <button class="keyboard-key" data-key="'">'</button>
            <button class="keyboard-key" data-key='"'>"</button>
            <button class="keyboard-key" data-key="<">&lt;</button>
            <button class="keyboard-key" data-key=">">&gt;</button>
            <button class="keyboard-key" data-key="/">/</button>
            <button class="keyboard-key" data-key="?">?</button>
        </div>
        
        <!-- Row 4: Back to letters -->
        <div class="keyboard-row">
            <button class="keyboard-key extra-wide" id="abcBtn" data-action="letters">ABC</button>
            <button class="keyboard-key wide" data-action="backspace">⌫</button>
            <button class="keyboard-key special extra-wide" data-action="done">✓ Done</button>
        </div>
    </div>
</div>