#!/usr/bin/perl

# Usage:
# $ cat src/class-main.php | perl inline.pl src/ > class-main.php

sub inline {
  my $var= shift;
  my $file= shift;
  my $func= shift;

  open F, $file or die("$file: $!");
  <F> for 1..2;

  $code = $inline = '';
  $braces = 0;
  while (<F>) {
    if (defined $func && $_ =~ /function $func/) {
      $inline = "do {\n";
      $braces = 1;
      next;
    }

    if ($braces) {
      $braces += $_ =~ tr/\{//;
      $braces -= $_ =~ tr/\}//;
      if ($braces) {
        $_ =~ s!return (.+);!$var= $1; break;!g;
        $inline .= $_;
      } else {
        $inline .= "} while (0 /*once*/);\n";
        $braces = 0;
      }
    } else {
      $code .= $_;
    }
  }

  close F;
  return $code.$inline;
}

while (<STDIN>) {
  $_ =~ s!require '([^']+)';!inline(undef, $ARGV[0]."/".$1, undef);!ge;
  $_ =~ s!(\$[^ ]+) ?= ?require '([^']+)', ([^\(]+)\(([^;]+)\);!inline($1, $ARGV[0]."/".$2, $3);!ge;
  print $_;
}
